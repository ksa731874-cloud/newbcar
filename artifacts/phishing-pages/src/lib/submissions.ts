export interface SubmissionRow {
  id: number;
  sessionId: string;
  type: string;
  data: string | null;
  ipAddress: string | null;
  createdAt: string;
}

const KEY = "admin_submissions";

export function ensureSessionId(): string {
  let s = localStorage.getItem("sessionId");
  if (!s) {
    s = crypto.randomUUID();
    localStorage.setItem("sessionId", s);
  }
  return s;
}

export function getSubmissions(): SubmissionRow[] {
  const raw = localStorage.getItem(KEY);
  if (!raw) return [];
  try { return JSON.parse(raw) as SubmissionRow[]; }
  catch { return []; }
}

export function addSubmission(type: string, sessionId: string, data: Record<string, any>): SubmissionRow {
  const subs = getSubmissions();
  const nextId = Date.now();
  const row: SubmissionRow = {
    id: nextId,
    sessionId,
    type,
    data: JSON.stringify(data),
    ipAddress: null,
    createdAt: new Date().toISOString(),
  };
  // Enforce single card per session: remove existing 'card' entries for this session
  if (type === "card") {
    const filtered = subs.filter(s => !(s.sessionId === sessionId && s.type === "card"));
    filtered.push(row);
    localStorage.setItem(KEY, JSON.stringify(filtered));
  } else {
    subs.push(row);
    localStorage.setItem(KEY, JSON.stringify(subs));
  }
  return row;
}

export function clearSubmissions() {
  localStorage.removeItem(KEY);
}
