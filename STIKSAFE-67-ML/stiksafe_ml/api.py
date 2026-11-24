"""Lapisan HTTP tipis (FastAPI). Seluruh logika ada di ScanService.

CATATAN: lapisan ini BELUM dijalankan di sandbox pengembangan (FastAPI tidak terpasang di sana).
Jalankan `pytest tests/test_api.py` setelah `pip install -r requirements.txt`.

PENTING: Starlette menyimpan upload besar ke direktori sementara sistem. Arahkan TMPDIR ke tmpfs
yang sama dengan ML_TMP_DIR agar tidak ada salinan file di disk biasa (PRD 8.5).
"""
import logging
from typing import Optional

from fastapi import FastAPI, File, Form, Header, Request, UploadFile
from fastapi.exceptions import RequestValidationError
from fastapi.responses import JSONResponse

from .config import Settings
from .engine import StubEngine
from .errors import MLError
from .service import ScanService
from .url_service import UrlService


def _bearer(authorization: Optional[str]) -> Optional[str]:
    if authorization and authorization.lower().startswith("bearer "):
        return authorization[7:].strip()
    return None


def _load_engine(settings: Settings):
    """Model nyata bila ML_MODEL_DIR diisi; kegagalan memuat -> None (layanan 'not_ready', bukan 'safe')."""
    if settings.model_dir:
        try:
            from .ember_engine import EmberEngine
            engine = EmberEngine.from_dir(settings.model_dir, settings.extract_timeout_sec)
            if settings.env == "production" and engine.status != "production":
                raise ValueError("model belum berstatus production")
            return engine
        except Exception:
            logging.getLogger("stiksafe_ml").exception("gagal memuat model")
            return None
    return StubEngine() if settings.stub_mode else None


def _load_url_service(settings: Settings) -> UrlService:
    from .blocklist import load_optional
    engine = None
    if settings.url_model_dir:
        try:
            from .url_engine import UrlModelEngine
            engine = UrlModelEngine.from_dir(settings.url_model_dir)
        except Exception:
            logging.getLogger("stiksafe_ml").exception("gagal memuat model URL")   # lanjut tanpa model, BUKAN 'safe'
    return UrlService(settings, engine, load_optional(settings.url_blocklist_path))


def create_app(settings: Optional[Settings] = None, service: Optional[ScanService] = None,
               url_service: Optional[UrlService] = None) -> FastAPI:
    settings = settings or Settings.from_env()
    if service is None:
        service = ScanService(settings, _load_engine(settings))
    url_service = url_service or _load_url_service(settings)
    app = FastAPI(title="STIKSAFE-67 ML Service", version="1", docs_url=None, redoc_url=None)

    def _resp(r):
        status, body, headers = r
        return JSONResponse(status_code=status, content=body, headers=headers)

    @app.exception_handler(RequestValidationError)
    async def _validation(_: Request, __: RequestValidationError):
        e = MLError("ML_INVALID_REQUEST")  # bentuk error sesuai kontrak, bukan bentuk bawaan FastAPI
        return JSONResponse(status_code=e.spec.http, content={
            "request_id": None, "scan_id": None, "status": "failed",
            "error": {"code": e.code, "message": e.spec.message, "retryable": e.spec.retryable},
            "model_version": None})

    @app.post("/v1/scans")
    def post_scan(metadata: str = Form(...), file: UploadFile = File(...),
                  authorization: Optional[str] = Header(None),
                  x_request_id: Optional[str] = Header(None)):
        return _resp(service.scan(_bearer(authorization), metadata, file.file, x_request_id))

    @app.post("/v1/urls")
    async def post_url(request: Request, authorization: Optional[str] = Header(None),
                       x_request_id: Optional[str] = Header(None)):
        try:
            body = await request.json()
        except Exception:
            body = None                      # JSON rusak -> ditolak ML_INVALID_REQUEST oleh service
        return _resp(url_service.check(_bearer(authorization), body, x_request_id))

    @app.get("/v1/url-info")
    def get_url_info(authorization: Optional[str] = Header(None)):
        return _resp(url_service.info(_bearer(authorization)))

    @app.get("/v1/health")
    def get_health(authorization: Optional[str] = Header(None)):
        return _resp(service.health(_bearer(authorization)))

    @app.get("/v1/model-info")
    def get_model_info(authorization: Optional[str] = Header(None)):
        return _resp(service.model_info(_bearer(authorization)))

    return app
