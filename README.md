# 🏥 Hospital Management System (EMR)

A full-featured **Hospital Management System (Electronic Medical Records - EMR)** built with PHP & MySQL, containerized with Docker for deployment on **Render**, and integrated with high-availability **Aiven Cloud MySQL** over SSL encryption.

---

## 🚀 Live Deployment & Links

| Service | Link / Endpoint |
|---|---|
| **App Homepage** | `https://hospital-management-system-xxxx.onrender.com/` |
| **Admin & Doctor Login** | `https://hospital-management-system-xxxx.onrender.com/login.php` |
| **Patient Portal** | `https://hospital-management-system-xxxx.onrender.com/login-patient.php` |
| **1-Click DB Installer & Diagnostics** | `https://hospital-management-system-xxxx.onrender.com/db_setup.php` |
| **GitHub Repository** | [maheshchouhanmsk12-sketch/Hospital-Management-System](https://github.com/maheshchouhanmsk12-sketch/Hospital-Management-System) |

---

## 🔑 Default Login Credentials

### 👑 System Administrator
* **Login URL:** `login.php?attempt=1`
* **Email:** `bensontesting@gmail.com` (or `admin@admin.com`)
* **Password:** `programming` (or `admin123`)
* **Role:** Administrator (Full system management, doctors, patients, reports, disease outbreaks)

### 👨‍⚕️ Doctor
* **Login URL:** `login.php?attempt=2`
* **Email:** `totonajoyce@gmail.com`
* **Password:** `12345`
* **Role:** Surgeon (Patient records, diagnosis, appointments, prescriptions)

* **Alternate Doctor:**
  * **Email:** `bensonwainaina30gmail.com`
  * **Password:** `hospital`
  * **Role:** Clinical Officer

### 🧑‍🦽 Patient Portal
* **Login URL:** `login-patient.php`
* **Phone Number:** `0725667841`
* **Patient Number:** `4066`

---

## ⚙️ Aiven Cloud MySQL Configuration

This application connects to Aiven Cloud MySQL with enforced SSL encryption (`require_secure_transport=ON`) and 5-digit custom ports.

### Environment Variables

| Variable | Description | Default / Example Value |
|---|---|---|
| `DB_SERVER` | Aiven MySQL Hostname | `mysql-1a85412b-maheshchouhanmsk12-c513.a.aivencloud.com` |
| `DB_PORT` | Custom 5-Digit Port | `26284` |
| `DB_USERNAME` | Database Username | `avnadmin` |
| `DB_PASSWORD` | Database Password | `YOUR_AIVEN_DB_PASSWORD` |
| `DB_NAME` | Database Name | `hospital` |
| `DB_SSL` | Enable SSL (`MYSQLI_CLIENT_SSL`) | `true` |
| `PORT` | Container Web Port | `10000` |
| `BASE_URL` | Application Root URL (optional) | `https://your-app.onrender.com` |

---

## 🛠️ Key Architectural & PHP 8.x Compatibility Upgrades

1. **PHP 8.2 Modern Autoloading:**
   * Replaced legacy `__autoload()` with modern, PSR-compatible `spl_autoload_register()`.
2. **Aiven Cloud SSL Handshake:**
   * Enabled `mysqli_init()`, `ssl_set(NULL, NULL, NULL, NULL, NULL)` and `MYSQLI_CLIENT_SSL`.
   * Configured PDO with `PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false`.
3. **Null-Safety & Result Verification:**
   * Added checks `if ($qry && ($qry instanceof PDOStatement || $qry instanceof mysqli_result))` to prevent fatal errors when tables are empty or being initialized.
4. **Render Reverse Proxy & Port 10000 Fix:**
   * Added `UseCanonicalName Off`, `UseCanonicalPhysicalPort Off`, and `ServerName localhost` in Apache to prevent the internal port `:10000` from leaking into public redirects.
   * Smart trailing slash handling and port stripping via `.htaccess`.
5. **1-Click Web Database Setup (`db_setup.php`):**
   * Visual health-check dashboard verifying connection latency, SSL encryption, schema status, and admin account readiness.
   * 1-Click importer executing with `SET SESSION sql_require_primary_key=0;` and `SET FOREIGN_KEY_CHECKS=0;`.

---

## 🚢 Deploying to Render

1. Log in to [Render.com](https://render.com).
2. Click **New +** &rarr; **Web Service**.
3. Connect your GitHub repository: `maheshchouhanmsk12-sketch/Hospital-Management-System`.
4. Choose **Docker** as the Runtime.
5. In **Environment Variables**, verify:
   * `DB_SERVER`: `mysql-1a85412b-maheshchouhanmsk12-c513.a.aivencloud.com`
   * `DB_PORT`: `26284`
   * `DB_USERNAME`: `avnadmin`
   * `DB_PASSWORD`: (Enter your Aiven DB password)
   * `DB_NAME`: `hospital`
   * `DB_SSL`: `true`
   * `PORT`: `10000`
6. Click **Deploy Web Service**.
7. Once deployed, open your live URL or navigate to `/db_setup.php` to verify database health.
