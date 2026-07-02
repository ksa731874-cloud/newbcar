# Database Setup Guide

## Prerequisites

- Node.js 18+
- pnpm 8+
- Neon PostgreSQL account (or any PostgreSQL database)

## Step 1: Get Your Neon Connection String

1. Go to [Neon Console](https://console.neon.tech)
2. Create a new project or select an existing one
3. Go to **Dashboard** → **Connection Details**
4. Copy the **Connection string**

It should look like:
```
postgresql://user:password@host/database?sslmode=require
```

## Step 2: Configure Environment Variables

### Option A: Create `.env` file (recommended for local development)

```bash
# Create .env file in artifacts/api-server/
cp artifacts/api-server/.env.example artifacts/api-server/.env
```

Edit `artifacts/api-server/.env`:
```env
DATABASE_URL=postgresql://your-username:your-password@your-host/your-database?sslmode=require
ADMIN_PASSWORD=your-secure-password
```

### Option B: Set environment variable directly

```bash
export DATABASE_URL="postgresql://your-username:your-password@your-host/your-database?sslmode=require"
```

## Step 3: Push Schema to Database

### Using the setup script:
```bash
node scripts/setup-db.mjs
```

### Or using pnpm directly:
```bash
cd lib/db
pnpm push
```

### Or using npx:
```bash
cd lib/db
npx drizzle-kit push
```

## Step 4: Verify Setup

Check your database - you should see two tables:
- `submissions` - stores all form submissions
- `admin_sessions` - stores admin authentication sessions

## Troubleshooting

### Connection refused
- Make sure your Neon project is active
- Check that your IP is allowed in Neon settings

### SSL required
- Ensure `?sslmode=require` is in your connection string
- Neon requires SSL connections

### Authentication failed
- Double-check your username and password
- Make sure the database name is correct

## Commands Reference

```bash
# Push schema (create/update tables)
pnpm --filter @workspace/db push

# Force push (drop and recreate tables)
pnpm --filter @workspace/db push-force

# Generate migration files
pnpm --filter @workspace/db generate

# Apply migrations
pnpm --filter @workspace/db migrate

# Open Drizzle Studio (visual DB editor)
pnpm --filter @workspace/db studio
```
