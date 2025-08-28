<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('sistema_plantilla');
    session_start();
}