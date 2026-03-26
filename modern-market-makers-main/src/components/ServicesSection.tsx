import { motion } from "framer-motion";
import {
  Globe,
  Video,
  Mail,
  BarChart3,
  Settings,
  Megaphone,
} from "lucide-react";

const services = [
  {
    icon: Globe,
    title: "Website Development",
    description:
      "Custom-built websites designed for performance, SEO, and conversion — plus ongoing maintenance to keep everything running smoothly.",
  },
  {
    icon: Video,
    title: "Content Creation",
    description:
      "High-quality digital and audiovisual content that captures your brand story and engages your target audience across every channel.",
  },
  {
    icon: Megaphone,
    title: "Social Media Management",
    description:
      "Strategic blog and social media management to grow your community, increase brand awareness, and drive meaningful engagement.",
  },
  {
    icon: Mail,
    title: "Email Marketing",
    description:
      "Data-driven email campaigns that nurture leads, retain customers, and deliver consistent ROI through personalized communication.",
  },
  {
    icon: BarChart3,
    title: "Lead Generation & Nurturing",
    description:
      "End-to-end lead generation, qualification, and nurturing strategies designed to fill your pipeline with high-quality prospects.",
  },
  {
    icon: Settings,
    title: "Marketing Automation",
    description:
      "Seamless integration of HubSpot, GoHighLevel, and other tools to automate marketing, sales, and customer support workflows.",
  },
];

const ServicesSection = () => {
  return (
    <section id="services" className="section-padding bg-section-alt">
      <div className="max-w-7xl mx-auto">
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.5 }}
          className="text-center mb-16"
        >
          <p className="text-primary font-semibold text-sm tracking-widest uppercase mb-4">
            What We Do
          </p>
          <h2 className="font-display font-bold text-3xl md:text-4xl lg:text-5xl text-foreground mb-4">
            Our <span className="text-gradient">Services</span>
          </h2>
          <p className="text-muted-foreground text-lg max-w-2xl mx-auto">
            A comprehensive suite of digital solutions to accelerate your
            business growth from every angle.
          </p>
        </motion.div>

        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {services.map((service, i) => (
            <motion.div
              key={service.title}
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-60px" }}
              transition={{ duration: 0.4, delay: i * 0.1 }}
              className="group p-8 rounded-2xl bg-card border border-border hover:border-primary/40 hover:shadow-lg hover:shadow-primary/5 transition-all"
            >
              <div className="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-5 group-hover:bg-primary/20 transition-colors">
                <service.icon className="w-6 h-6 text-primary" />
              </div>
              <h3 className="font-display font-semibold text-xl text-foreground mb-3">
                {service.title}
              </h3>
              <p className="text-muted-foreground leading-relaxed text-sm">
                {service.description}
              </p>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default ServicesSection;
