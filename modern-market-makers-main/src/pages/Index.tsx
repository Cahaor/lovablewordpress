import { useNavigate } from "react-router-dom";
import Navbar from "@/components/Navbar";
import HeroSection from "@/components/HeroSection";
import AboutSection from "@/components/AboutSection";
import ServicesSection from "@/components/ServicesSection";
import ContactSection from "@/components/ContactSection";
import Footer from "@/components/Footer";
import { Button } from "@/components/ui/button";
import { FileUp } from "lucide-react";

const Index = () => {
  const navigate = useNavigate();
  
  return (
    <div className="min-h-screen">
      <Navbar />
      <HeroSection />
      <AboutSection />
      <ServicesSection />
      <ContactSection />
      <Footer />
      
      {/* Floating Converter Button */}
      <div className="fixed bottom-6 right-6 z-50">
        <Button 
          onClick={() => navigate('/converter')}
          size="lg"
          className="gap-2 shadow-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700"
        >
          <FileUp className="w-5 h-5" />
          Lovable → WordPress
        </Button>
      </div>
    </div>
  );
};

export default Index;
