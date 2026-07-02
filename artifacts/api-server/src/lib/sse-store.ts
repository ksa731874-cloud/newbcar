import type { Response } from "express";

export interface SSEMessage {
  event: string;
  data: string;
  id?: string;
}

export interface ClientConnection {
  sessionId: string;
  response: Response;
  lastEventId?: string;
}

// Store all connected clients
const clients = new Map<string, Set<Response>>();

// Register a new client connection
export function registerClient(sessionId: string, response: Response): void {
  if (!clients.has(sessionId)) {
    clients.set(sessionId, new Set());
  }
  clients.get(sessionId)!.add(response);
  console.log(`[SSE] Client connected: ${sessionId} (total: ${getSessionClientCount(sessionId)})`);
}

// Unregister a client connection
export function unregisterClient(sessionId: string, response: Response): void {
  const sessionClients = clients.get(sessionId);
  if (sessionClients) {
    sessionClients.delete(response);
    if (sessionClients.size === 0) {
      clients.delete(sessionId);
    }
    console.log(`[SSE] Client disconnected: ${sessionId} (remaining: ${getSessionClientCount(sessionId)})`);
  }
}

// Get number of connected clients for a session
export function getSessionClientCount(sessionId: string): number {
  return clients.get(sessionId)?.size ?? 0;
}

// Check if a session has any connected clients
export function hasConnectedClients(sessionId: string): boolean {
  return getSessionClientCount(sessionId) > 0;
}

// Send an SSE event to all clients of a specific session
export function sendSSEMessage(sessionId: string, event: string, data: object | string): void {
  const sessionClients = clients.get(sessionId);
  if (!sessionClients || sessionClients.size === 0) {
    console.log(`[SSE] No clients connected for session: ${sessionId}`);
    return;
  }

  const messageId = Date.now().toString();
  const dataStr = typeof data === 'string' ? data : JSON.stringify(data);
  const message: SSEMessage = {
    event,
    data: dataStr,
    id: messageId,
  };

  const formattedMessage = formatSSEMessage(message);
  
  console.log(`[SSE] Sending "${event}" to session ${sessionId} (clients: ${sessionClients.size})`);
  console.log(`[SSE] Data: ${dataStr.substring(0, 100)}${dataStr.length > 100 ? '...' : ''}`);

  for (const response of sessionClients) {
    try {
      response.write(formattedMessage);
    } catch (error) {
      console.error(`[SSE] Error sending to client:`, error);
    }
  }
}

// Send heartbeat to keep connection alive
export function sendHeartbeat(sessionId: string): void {
  const sessionClients = clients.get(sessionId);
  if (!sessionClients) return;

  for (const response of sessionClients) {
    try {
      response.write(`: heartbeat ${Date.now()}\n\n`);
    } catch (error) {
      // Ignore heartbeat errors
    }
  }
}

// Format SSE message according to spec
function formatSSEMessage(message: SSEMessage): string {
  let result = "";
  if (message.id) {
    result += `id: ${message.id}\n`;
  }
  if (message.event) {
    result += `event: ${message.event}\n`;
  }
  result += `data: ${message.data}\n\n`;
  return result;
}

// Get total number of connected clients
export function getTotalClientCount(): number {
  let total = 0;
  for (const sessionClients of clients.values()) {
    total += sessionClients.size;
  }
  return total;
}

// Get all active session IDs
export function getActiveSessions(): string[] {
  return Array.from(clients.keys());
}

// Clean up all connections (for shutdown)
export function cleanupAll(): void {
  console.log(`[SSE] Cleaning up ${getTotalClientCount()} connections`);
  for (const [sessionId, sessionClients] of clients) {
    for (const response of sessionClients) {
      try {
        response.end();
      } catch (error) {
        // Ignore
      }
    }
  }
  clients.clear();
}
