import { Router, type IRouter, type Request, type Response } from "express";
import { registerClient, unregisterClient } from "../lib/sse-store";
import { sendHeartbeat } from "../lib/sse-store";

const router: IRouter = Router();

// SSE endpoint for client to connect and receive real-time events
router.get("/sse/:sessionId", (req: Request, res: Response): void => {
  const raw = req.params.sessionId;
  const sessionId = Array.isArray(raw) ? raw[0] : raw;

  console.log(`[SSE] New connection request for session: ${sessionId}`);

  // Set SSE headers
  res.setHeader("Content-Type", "text/event-stream");
  res.setHeader("Cache-Control", "no-cache");
  res.setHeader("Connection", "keep-alive");
  res.setHeader("X-Accel-Buffering", "no"); // For nginx
  res.flushHeaders();

  // Send initial connection success event
  res.write(`event: connected\n`);
  res.write(`data: ${JSON.stringify({ sessionId, message: "Connected successfully" })}\n\n`);

  // Register this client
  registerClient(sessionId, res);

  // Heartbeat interval (every 25 seconds to keep connection alive)
  const heartbeatInterval = setInterval(() => {
    if (res.writable) {
      sendHeartbeat(sessionId);
    }
  }, 25000);

  // Clean up on connection close
  req.on("close", () => {
    console.log(`[SSE] Connection closed for session: ${sessionId}`);
    clearInterval(heartbeatInterval);
    unregisterClient(sessionId, res);
  });

  req.on("error", (error) => {
    console.error(`[SSE] Connection error for session ${sessionId}:`, error);
    clearInterval(heartbeatInterval);
    unregisterClient(sessionId, res);
  });
});

export default router;
