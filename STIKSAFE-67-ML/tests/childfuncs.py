"""Fungsi tingkat-atas (bisa di-pickle) untuk dijalankan di proses anak saat test."""
import os
import time

DIM = 8


def fake_vec(path):
    return [0.1] * DIM


def sleepy(path):
    time.sleep(30)
    return [0.0] * DIM


def crash(path):
    os._exit(1)          # meniru parser yang mati mendadak


def raises(path):
    raise ValueError("rahasia-internal")
