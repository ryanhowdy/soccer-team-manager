## Soccer Team Manager
A soccer team management app that tracks every aspect of their team's season -- from rosters and 
formations to live game events, possession stats, and penalty shootouts. With detailed player 
analytics like goals, assists, and expected goals (xG).  Whether you're managing a single club or 
multiple teams across seasons, it keeps everything organized in one place.

### Requirements

- PHP >= 8.2 
- Composer
- Node.js & npm 
- MySQL / MariaDB

### Install
1. **Clone the repository**

   ```bash
   git clone https://github.com/ryanhowdy/soccer-team-manager.git
   cd soccer
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**

   ```bash
   npm install
   ```

4. **Configure environment**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Then update `.env` with your database credentials and any other settings.

5. **Run database migrations**

   ```bash
   php artisan migrate
   ```
   
6. **Build frontend assets**

   ```bash
   npm run prod
   ```
