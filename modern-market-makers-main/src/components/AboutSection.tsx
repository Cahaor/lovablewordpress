import { motion } from "framer-motion";
import { Target, TrendingUp, Users } from "lucide-react";

const stats = [
  { icon: Target, label: "Strategies Delivered", value: "200+" },
  { icon: Users, label: "Clients Served", value: "150+" },
  { icon: TrendingUp, label: "Avg. Growth Rate", value: "3x" },
];

const AboutSection = () => {
  return (
    <section id="about" className="section-padding bg-background">
      <div className="max-w-7xl mx-auto">
        <div className="grid lg:grid-cols-2 gap-16 items-center">
          {/* Text */}
          <motion.div
            initial={{ opacity: 0, x: -40 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.6 }}
          >
            <p className="text-primary font-semibold text-sm tracking-widest uppercase mb-4">
              Who We Are
            </p>
            <h2 className="font-display font-bold text-3xl md:text-4xl lg:text-5xl text-foreground mb-6 leading-tight">
              Your Full-Service <span className="text-gradient">Digital Partner</span>
            </h2>
            <p className="text-muted-foreground text-lg leading-relaxed mb-6">
              360 Videomarketers is an Ireland-based agency specializing in helping
              small and medium-sized businesses create, manage, and monetize their
              digital presence. We handle website development and maintenance,
              digital and audiovisual content creation, as well as blog, social
              media, and email marketing campaign management.
            </p>
            <p className="text-muted-foreground text-lg leading-relaxed">
              We implement lead generation, qualification, and nurturing
              strategies, integrating tools such as HubSpot and GoHighLevel to
              automate marketing, sales, and customer support — delivering a
              comprehensive commercial process designed to drive demand and
              measurable results.
            </p>
          </motion.div>

          {/* Stats */}
          <motion.div
            initial={{ opacity: 0, x: 40 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="grid gap-6"
          >
            {stats.map((stat) => (
              <div
                key={stat.label}
                className="flex items-center gap-6 p-6 rounded-2xl bg-card border border-border shadow-sm hover:shadow-md hover:border-primary/30 transition-all"
              >
                <div className="flex-shrink-0 w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center">
                  <stat.icon className="w-7 h-7 text-primary" />
                </div>
                <div>
                  <p className="font-display font-bold text-3xl text-foreground">
                    {stat.value}
                  </p>
                  <p className="text-muted-foreground text-sm">{stat.label}</p>
                </div>
              </div>
            ))}
          </motion.div>
        </div>
      </div>
    </section>
  );
};

export default AboutSection;
