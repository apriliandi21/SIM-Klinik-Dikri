<?php

require_once '../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    // FIX: Path absolut untuk .htaccess & XAMPP
    header("Location: /klinik-dikri/");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SIM Klinik</title>
    
    <link rel="icon" type="image/png" href="/klinik-dikri/assets/image/favicon1.png">
    <link rel="apple-touch-icon" href="/klinik-dikri/assets/image/apple-touch-icon.png">
    <link rel="stylesheet" href="/klinik-dikri/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="../assets/css/style.css"> 
    
    <style>
        body {
            /* Warna abu-abu muda dari Bootstrap */
            background-color: #f8f9fa; 
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="/klinik-dikri/admin/">
        SIM KLINIK (ADMIN)
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
      </ul>
      
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="/klinik-dikri/admin/">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/klinik-dikri/admin/data_obat">Data Obat</a> 
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/klinik-dikri/admin/manajemen_user">Manajemen User</a> 
        </li>
         <li class="nav-item ms-3">
           <a href="/klinik-dikri/logout" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin logout?');">
               Logout
           </a>
         </li>
      </ul>
    </div> </div>
</nav>

<div class="container mt-4"> <div class="row">
    <div class="col-md-12">
      <div class="card shadow-sm border-0">
        <div class="card-body p-4"> ```

