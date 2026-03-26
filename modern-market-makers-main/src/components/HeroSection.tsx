import { motion } from "framer-motion";
import heroBg from "@/assets/hero-bg.jpg";

const HeroSection = () => {
  return (
    <section
      id="home"
      className="relative min-h-screen flex items-center justify-center bg-hero overflow-hidden"
    >
      {/* Background image */}
      <div className="absolute inset-0">
        <img
          src={heroBg}
          alt=""
          className="w-full h-full object-cover opacity-40"
        />
        <div className="absolute inset-0 bg-gradient-to-b from-hero/60 via-hero/40 to-hero" />
      </div>

      <div className="relative z-10 max-w-5xl mx-auto text-center px-6 pt-20">
        <motion.p
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5 }}
          className="text-primary font-semibold text-sm md:text-base tracking-widest uppercase mb-6"
        >
          Ireland-Based Digital Agency
        </motion.p>

        <motion.h1
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.15 }}
          className="font-display font-bold text-4xl sm:text-5xl md:text-6xl lg:text-7xl text-hero-foreground leading-tight mb-6"
        >
          We Drive{" "}
          <span className="text-gradient">Digital Growth</span>
          <br />
          For Your Business
        </motion.h1>

        <motion.p
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.3 }}
          className="text-hero-foreground/70 text-lg md:text-xl max-w-2xl mx-auto mb-10 font-body"
        >
          From content creation to marketing automation — we help SMBs build,
          manage, and monetize their digital presence with measurable results.
        </motion.p>

        <motion.div
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.45 }}
          className="flex flex-col sm:flex-row items-center justify-center gap-4"
        >
          <a
            href="#contact"
            className="px-8 py-3.5 rounded-lg bg-primary text-primary-foreground font-semibold text-base hover:brightness-110 transition shadow-lg shadow-primary/25"
          >
            Start Your Project
          </a>
          <a
            href="#services"
            className="px-8 py-3.5 rounded-lg border border-hero-foreground/20 text-hero-foreground font-semibold text-base hover:border-primary hover:text-primary transition"
          >
            Our Services
          </a>
        </motion.div>
      </div>
    </section>
  );
};

export default HeroSection;
