# ⬡ FileVault — Secure File Sharing Platform

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

> A secure file sharing web app built with PHP and MySQL.
> Upload, encrypt, share via link, and track every download.

🌐 **Live Demo:** [filevault.great-site.net](http://filevault.great-site.net/register.php)
<img width="1551" height="650" alt="image" src="https://github.com/user-attachments/assets/351b16b3-11aa-416f-976b-f5c52d46ea94" />
<img width="531" height="793" alt="image" src="https://github.com/user-attachments/assets/4bbeb381-cdac-4304-ba85-d4c635a142e7" />
<img width="580" height="793" alt="image" src="https://github.com/user-attachments/assets/147e8adc-3a4d-470b-83f9-d71b33bdcab9" />
<img width="1654" height="886" alt="image" src="https://github.com/user-attachments/assets/c9203353-7ea5-4f5a-aa40-d1b7501e9331" />
<img width="1725" height="822" alt="image" src="https://github.com/user-attachments/assets/cdae6e0f-ff54-4ef0-8e08-5df293d77be0" />

## ✨ Features

- 🔐 Register & Login with secure password hashing
- ⬆️ Upload files with type and size validation
- 🔒 Optional XOR encryption before storing on server
- 🌐 Public / Private file permission control
- 🔗 Unique shareable link for every file
- 📊 Download tracking with IP, username and timestamp
- 🛡️ Protected against SQL injection, XSS and CSRF

---

## 🛠️ Tech Stack

| | Technology |
|-|-----------|
| Backend | PHP 8.0+ |
| Database | MySQL |
| Frontend | HTML, CSS, JavaScript |
| Hosting | InfinityFree |

---

## ⚙️ Setup (Local)

1. Clone this repo and copy to `htdocs/filevault/`
2. Import `database.sql` in phpMyAdmin
3. Update `config.php` with your DB details
4. Open `http://localhost/filevault/register.php`

## ⚙️ Setup (InfinityFree)

1. Create account at [infinityfree.com](https://infinityfree.com)
2. Create MySQL database from Control Panel
3. Import `database.sql` via phpMyAdmin
4. Upload all files to `htdocs/` via File Manager
5. Create empty `uploads/` folder with 755 permission
6. Update `config.php` with InfinityFree DB credentials and your subdomain URL

---

## 🔒 How Encryption Works

When encryption is enabled, each byte of the file is **XOR'd** with a secret key before saving. The file on disk is unreadable without the key. On download, it is automatically decrypted.

> XOR cipher is used here for learning purposes. AES-256 is the industry standard for production apps.

---

## 🎓 About

Built as a **2nd Year B.Tech Computer Science** project at **NIET Greater Noida** to demonstrate PHP, MySQL, file handling, encryption, and web security concepts.

---

## 👩‍💻 Author

**Shreya Kushwaha** — [@shreyakushwaha39](https://github.com/shreyakushwaha39)

⭐ Star this repo if you found it helpful!
