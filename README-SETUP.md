# 🚀 Monthli Development Setup

**Quick start voor nieuwe developers - klaar in 2 minuten!**

## ⚡ One-Command Setup

```bash
./docker-dev.sh setup
```

Dit commando doet alles automatisch:
- ✅ Configureert environment variabelen
- ✅ Bouwt Docker images
- ✅ Start alle services (PHP, PostgreSQL, Redis, Nginx)
- ✅ Genereert Laravel app key
- ✅ Draait database migraties
- ✅ Maakt admin gebruiker aan
- ✅ Installeert dependencies

**That's it! Je bent klaar om te ontwikkelen.** 🎉

---

## 📊 Admin Panel Toegang

Na setup kun je direct inloggen:

- **URL**: http://localhost:8000/admin
- **Email**: admin@monthli.com  
- **Password**: admin123

---

## 🛠️ Handige Commando's

```bash
# Development
./docker-dev.sh logs        # Bekijk logs
./docker-dev.sh shell       # Open app shell
./docker-dev.sh artisan     # Run Laravel commando's
./docker-dev.sh migrate     # Database migraties
./docker-dev.sh restart     # Herstart services

# Status
./docker-dev.sh status      # Container status
./docker-dev.sh down        # Stop alles

# Production
./docker-dev.sh prod        # Start productie omgeving
```

---

## 📁 Project Structuur

```
Monthli/
├── app/Filament/           # Admin panel resources & widgets
├── docker/                 # Docker configuratie
├── database/migrations/    # Database schema
├── routes/web.php         # Web routes
└── docker-dev.sh          # Development helper script
```

---

## 🐛 Troubleshooting

**Port 8000 al in gebruik?**
```bash
./docker-dev.sh down
./docker-dev.sh setup
```

**Database problemen?**
```bash
./docker-dev.sh shell
php artisan migrate:fresh --seed
```

**Setup gefaald of incomplete?**
```bash
./docker-dev.sh reset    # Clear setup state
./docker-dev.sh setup    # Re-run from scratch
```

**Complete fresh start needed?**
```bash
./docker-dev.sh rollback # Complete teardown (removes all data!)
./docker-dev.sh setup    # Fresh setup from zero
```

**Services niet bereikbaar?**
```bash
./docker-dev.sh status   # Check container status
./docker-dev.sh logs     # Check for errors
```

**Permission errors?**
```bash
sudo chmod +x docker-dev.sh
```

---

## 🎯 Ready to Code!

- Filament admin panel draait op http://localhost:8000/admin
- Live code reload is ingeschakeld
- Database persisted in Docker volumes
- Alle Laravel tools beschikbaar via `./docker-dev.sh artisan`

**Happy coding! 🚀**
