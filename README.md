# 🏥 Hospital Management System (EMR) - Live on Render

Full-fledged **Hospital Management System (Electronic Medical Records - EMR)** built in PHP 8.2 & MySQL, containerized with Docker, deployed on **Render**, and integrated with **Aiven Cloud MySQL** over SSL encryption.

---

## 🌐 Live Application Links

| Portal | Direct URL |
|---|---|
| 🏠 **Main Homepage** | [https://hospital-management-system-ktrh.onrender.com/](https://hospital-management-system-ktrh.onrender.com/) |
| 🔐 **Main Login Page** | [https://hospital-management-system-ktrh.onrender.com/login.php](https://hospital-management-system-ktrh.onrender.com/login.php) |
| 👑 **Admin Direct Login** | [https://hospital-management-system-ktrh.onrender.com/login.php?attempt=1](https://hospital-management-system-ktrh.onrender.com/login.php?attempt=1) |
| 👨‍⚕️ **Doctor Direct Login** | [https://hospital-management-system-ktrh.onrender.com/login.php?attempt=2](https://hospital-management-system-ktrh.onrender.com/login.php?attempt=2) |
| 🧑‍🦽 **Patient Portal** | [https://hospital-management-system-ktrh.onrender.com/login-patient.php](https://hospital-management-system-ktrh.onrender.com/login-patient.php) |
| 🛠️ **1-Click DB Setup & Diagnostics** | [https://hospital-management-system-ktrh.onrender.com/db_setup.php](https://hospital-management-system-ktrh.onrender.com/db_setup.php) |
| 🐙 **GitHub Repository** | [maheshchouhanmsk12-sketch/Hospital-Management-System](https://github.com/maheshchouhanmsk12-sketch/Hospital-Management-System) |

---

## 🔑 All Login Credentials & Access Details

### 👑 1. System Administrator Accounts

#### Admin Account 1 (Primary)
* **Direct Login URL:** `https://hospital-management-system-ktrh.onrender.com/login.php?attempt=1`
* **Email:** `bensontesting@gmail.com`
* **Password:** `programming`
* **Status:** `1` (Admin)
* **Privileges:** Full administrative rights, Add/Remove Doctors, View Outbreaks, HIV Records, Patient Records, and Reports.

#### Admin Account 2 (Secondary)
* **Direct Login URL:** `https://hospital-management-system-ktrh.onrender.com/login.php?attempt=1`
* **Email:** `admin@admin.com`
* **Password:** `admin123`
* **Status:** `1` (Admin)
* **Role:** `Administrator`

---

### 👨‍⚕️ 2. Doctor Accounts

#### Doctor 1 (Surgeon)
* **Direct Login URL:** `https://hospital-management-system-ktrh.onrender.com/login.php?attempt=2`
* **Email:** `totonajoyce@gmail.com`
* **Password:** `12345`
* **Status:** `2` (Doctor)
* **Role:** `Surgeon`
* **Capabilities:** Diagnose patients, add appointments, prescribe medicine, view hospital records.

#### Doctor 2 (Clinical Officer)
* **Direct Login URL:** `https://hospital-management-system-ktrh.onrender.com/login.php?attempt=2`
* **Email:** `bensonwainaina30gmail.com`
* **Password:** `hospital`
* **Status:** `2` (Doctor)
* **Role:** `Clinical Officer`

---

### 🧑‍🦽 3. Patient Portal Accounts

* **Patient Login URL:** `https://hospital-management-system-ktrh.onrender.com/login-patient.php`

#### Patient 1:
* **Phone Number:** `0725667841`
* **Patient Number:** `4066`
* **Name:** Benson Karue (Age: 40, Gender: Female/Male)

#### Patient 2:
* **Phone Number:** `0713345678`
* **Patient Number:** `1234`
* **Name:** Testing Data (Age: 40, Gender: Male)

---

## 🗄️ Aiven Cloud MySQL Configuration

| Parameter | Value |
|---|---|
| **Host** | `mysql-1a85412b-maheshchouhanmsk12-c513.a.aivencloud.com` |
| **Port** | `26284` |
| **User** | `avnadmin` |
| **Database** | `hospital` |
| **SSL Encryption** | Required (`MYSQLI_CLIENT_SSL` / `require_secure_transport=ON`) |
| **MySQL Version** | 8.4.11 |
| **Tables (5)** | `appointment`, `hiv`, `outbreaks`, `patients`, `users` |

### Environment Variables on Render

| Key | Value / Note |
|---|---|
| `DB_SERVER` | `mysql-1a85412b-maheshchouhanmsk12-c513.a.aivencloud.com` |
| `DB_PORT` | `26284` |
| `DB_USERNAME` | `avnadmin` |
| `DB_PASSWORD` | *(Your Aiven DB Password)* |
| `DB_NAME` | `hospital` |
| `DB_SSL` | `true` |
| `PORT` | `10000` |
| `BASE_URL` | `https://hospital-management-system-ktrh.onrender.com` |

---

## 🛠️ Key Architectural & PHP 8.x Compatibility Upgrades

1. **Linux Case-Insensitive Autoloader (`importance.php`):**
   * Solved Linux case-sensitivity issues where uppercase `CONFIG::SYSTEM_NAME` crashed on Linux filesystems. Added PSR-compatible case-insensitive scanning and class aliasing.
2. **PHP 8 Syntax Compliance (`inc/header.inc.php`):**
   * Fixed unspaced PHP comment tags (`<?php//`) that triggered `Fatal error: Undefined constant "php"`.
3. **Render Reverse Proxy & Dynamic Port Binding (`start.sh` & `.htaccess`):**
   * Configured dynamic `<VirtualHost *:${PORT}>` and `ports.conf`.
   * Added `UseCanonicalName Off`, `UseCanonicalPhysicalPort Off`, and `ServerName localhost` to eliminate internal port (`:10000`) leakage in redirects.
   * Strip Windows CRLF line endings via `Dockerfile` to guarantee flawless execution in Linux Docker containers.
4. **Aiven SSL Handshake:**
   * Full support for 5-digit custom ports and SSL certificate verification bypass (`PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false`).
5. **1-Click Web Diagnostic Tool (`db_setup.php`):**
   * Real-time connectivity health check, table counter, and schema installer.
