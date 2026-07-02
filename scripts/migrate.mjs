#!/usr/bin/env node
/**
 * Database Migration Script
 * 
 * This script runs drizzle-kit push to sync the schema with the database.
 * Run this AFTER deployment to create/update tables.
 * 
 * Usage:
 *   node scripts/migrate.mjs
 * 
 * Or with pnpm:
 *   pnpm --filter @workspace/db push
 */

import { config } from "dotenv";
import { resolve, dirname } from "path";
import { fileURLToPath } from "url";
import { execSync } from "child_process";

const __dirname = dirname(fileURLToPath(import.meta.url));

// Load environment variables from various locations
config({ path: resolve(__dirname, "..", "artifacts/api-server/.env") });
config({ path: resolve(__dirname, "..", ".env") });

console.log("===========================================");
console.log("  Database Migration Script");
console.log("===========================================\n");

// Check if DATABASE_URL is set
if (!process.env.DATABASE_URL) {
  console.error("❌ ERROR: DATABASE_URL is not set!");
  console.error("\nPlease set your database URL:");
  console.error('  export DATABASE_URL="postgresql://user:pass@host/db?sslmode=require"');
  console.error("\nOr add it to artifacts/api-server/.env");
  process.exit(1);
}

// Mask the password for logging
const maskedUrl = process.env.DATABASE_URL.replace(/:([^@]+)@/, ":***@");
console.log("📦 Database:", maskedUrl);
console.log("");

console.log("🚀 Running drizzle-kit push...\n");

try {
  execSync("pnpm --filter @workspace/db push", {
    cwd: resolve(__dirname, ".."),
    stdio: "inherit",
    env: {
      ...process.env,
      DATABASE_URL: process.env.DATABASE_URL,
    },
  });
  
  console.log("\n✅ Migration completed successfully!");
  console.log("\nThe following tables should now exist:");
  console.log("  - submissions");
  console.log("  - admin_sessions");
  
} catch (error) {
  console.error("\n❌ Migration failed!");
  console.error("\nCommon issues:");
  console.error("  1. Database connection refused - check your host/port");
  console.error("  2. Authentication failed - check username/password");
  console.error("  3. SSL required - add ?sslmode=require to connection string");
  console.error("  4. Database doesn't exist - create it in Neon console first");
  
  process.exit(1);
}
