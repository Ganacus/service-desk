**MwananchiDesk – Public Service Request System**  
   
    
   
  A mini eCitizen-style web app where citizens report public-service problems (broken streetlights, water issues, garbage collection…) and administrators manage them through to resolution.  
   
  Built with **plain PHP 8.1+ (no framework), PDO + SQLite, and hand-written CSS**.  
   
   No Composer, no build step, no database server to install.  
   
  **Features**  
  
|  |  |  
| - | - |  
|   |    |  
|   |    |  
| **Requirement** |  **Where** |  
| Citizen registration / login |  `public/register.php`, `public/login.php` |  
| Submit request (category, location, description, optional image) |  `public/submit.php` |  
| Admin dashboard (view, filter, manage all requests) |  `public/admin/index.php`, `public/admin/view.php` |  
| Status tracking: Pending → In Progress → Resolved |  progress bar + update history on every request |  
| Basic analytics: open vs resolved |  stat cards + bar charts on the admin dashboard |  
  
   
   
   
   
  **Bonus features:** request update timeline with admin notes · search & filter by status/category ·  
   
   
   resolution-rate metric · category breakdown chart · responsive layout · CSRF protection ·  
   
   secure image validation · Docker deployment.  
   
  **Security notes**  
- Passwords hashed with `password_hash()` (bcrypt/argon, via `PASSWORD_DEFAULT`).  
- All DB access uses PDO **prepared statements** (no SQL injection).  
- All output escaped with `htmlspecialchars` (no XSS).  
- CSRF token on every POST form; session ID regenerated on login.  
- Uploads: size limit (2 MB), real MIME check (`finfo` + `getimagesize`), random file names,script execution disabled in `public/uploads`.  
- Role checks: citizens can only open their own requests; `/admin/*` is admin-only.  
   
  **Run locally**  
  Requirements: **PHP 8.1+** with the `pdo_sqlite`, `fileinfo` and `mbstring` extensions  
   
  (all enabled by default in most PHP installs, XAMPP included – on XAMPP make sure `extension=pdo_sqlite` is enabled in `php.ini`).  
```  
git clone <your-repo-url>      
   cd service-desk      
   php -S localhost:8000 -t public      
         
      
   
```  
Open [http://localhost:8000. The SQLite database (`storage/app.sqlite`) and tables are created automatically on first request.  
   
    
   
  **Default admin account**](http://localhost:8000 "http://localhost:8000")  
  
|  |  |  
| - | - |  
|   |    |  
|   |    |  
| **Email** |  **Password** |  
| `admin@example.com` |  `Admin@12345` |  
  
   
    
   
  Override with environment variables `ADMIN_EMAIL` and `ADMIN_PASSWORD` **before the first run**  
   (the admin is only created if none exists). Citizens register themselves via the Register page.  
   
    
   
  **Project structure**  
```  
public/            Web root (only this folder is exposed)      
     index.php        Landing page      
     register.php, login.php, logout.php      
     dashboard.php    Citizen's own requests      
     submit.php       New request form (+ image upload)      
     request.php      Citizen request detail & timeline      
     admin/           Admin-only pages (dashboard + manage request)      
     assets/style.css Styling      
     uploads/         Uploaded images (script execution blocked)      
   src/      
     bootstrap.php    Loads everything, starts session      
     config.php       Constants (categories, statuses, paths)      
     db.php           PDO connection, auto-migration, admin seeding      
     auth.php         Login/session/role helpers      
     helpers.php      Escaping, flash messages, CSRF, upload handling      
     views/           Shared header, footer, request detail partial      
   storage/           SQLite database (git-ignored)      
   Dockerfile         Container image for deployment      
         
      
   
```  
**Deployment**  
  Vercel does not run PHP natively, so deploy with Docker on a host that does:  
   
   **Render.com (free tier)**  
1. Push this repo to GitHub.  
2. New → *Web Service* → connect the repo → Runtime: **Docker**.  
3. Add env vars `ADMIN_EMAIL` and `ADMIN_PASSWORD`.  
4. Deploy and share the generated `onrender.com` URL.  
   
  *Free tiers have an ephemeral disk: the SQLite file and uploaded images reset on redeploy/restart.*  
   
    
   *Attach a persistent disk (mount `/var/www/html/storage` and `/var/www/html/public/uploads`)or switch the PDO connection in `src/db.php` to MySQL for permanent storage.*  
   
  Any PHP host (e.g. InfinityFree, shared hosting) also works: upload the project, point the domain's document root at `public/`, and make `storage/` and `public/uploads/` writable.  
 If you must host inside a subfolder, set the `APP_BASE` environment variable (e.g. `/service-desk`).  
   
  **Possible improvements**  
  Email notifications, pagination, login rate-limiting, password reset, MySQL support, automated tests.  
