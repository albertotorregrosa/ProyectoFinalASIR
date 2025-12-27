# GIRAHUB – Automatización de Reservas con n8n (MVP)

## Descripción
GIRAHUB es un proyecto académico para **gestionar y reservar aulas y dispositivos** en un entorno educativo mediante una infraestructura mínima viable (MVP).

## Objetivos del MVP (Fase 3)
- Desplegar una infraestructura base operativa.
- Validar conectividad y funcionamiento de servicios.
- Documentar despliegue, procedimientos e incidencias.

## Arquitectura (MVP)
- **Web**: Hosting compartido (presentación/acceso a la plataforma).
- **Backend**: VPS en Hostinger con Docker.
- **Automatización**: n8n + Traefik.
- **Base de datos**: MariaDB (reservas/horarios/recursos).

## Estado actual
En funcionamiento:
- VPS (Hostinger) con Ubuntu Server
- Docker y Docker Compose
- MariaDB operativa
- n8n accesible por web
- Red Docker funcional

Pendiente (siguientes fases):
- Integración completa de la web con la BD/LDAP
- Modelado final de tablas y workflows completos de reservas

## Tecnologías
- Docker / Docker Compose
- MariaDB
- n8n
- Traefik
- Ubuntu Server
- Hostinger (VPS + hosting web)

## Despliegue rápido (VPS)
```bash
docker compose up -d
docker ps
docker logs <servicio>
docker compose down
