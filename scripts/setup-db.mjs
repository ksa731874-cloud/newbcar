#!/usr/bin/env node
/**
 * Database Setup Script
 * 
 * This script pushes the Drizzle schema to your PostgreSQL database.
 * 
 * Usage:
 *   1. Set DATABASE_URL in .env or environment variable
 *   2. Run: node scripts/setup-db.mjs
 * 
 * Or use pnpm:
 *   cd lib/db && pnpm push
 */

import { config } from "dotenv";
import { resolve, dirname } from "path";
import { fileURLToPath } from "url";

// Load environment variables
config({ path: resolve(process.cwd(), "artifacts/api-server/.env") });
config({ path: resolve(process.cwd(), ".env") });

const __dirname = dirname(fileURLToPath(import.meta.url));

if (!process.env.DATABASE_URL) {
  console.error("❌ DATABASE_URL is not set!");
  console.error("\nPlease set your database URL:");
  console.error("  export DATABASE_URL='postgresql://user:password@host/database?sslmode=require'");
  console.error("\nOr create a .env file in artifacts/api-server/ with:");
  console.error("  DATABASE_URL=postgresql://user:password@host/database?sslmode=require");
  process.exit(1);
}

console.log("🔄 Pushing schema to database...");
console.log(`📦 Database: ${process.env.DATABASE_URL.split("@")[1] || "unknown"}\n`);

// Run drizzle-kit push
import { execSync } from "child_process";

try {
  execSync("pnpm --filter @workspace/db push", {
    cwd: resolve(__dirname, ".."),
    stdio: "inherit",
    env: {
      ...process.env,
      DATABASE_URL: process.env.DATABASE_URL,
    },
  });
  console.log("\n✅ Schema pushed successfully!");
} catch (error) {
  console.error("\n❌ Failed to push schema!");
  console.error("Make sure your DATABASE_URL is correct and the database is accessible.");
  process.exit(1);
}
