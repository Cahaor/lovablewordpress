import logo from "@/assets/logo-horizontal.png";

const Footer = () => {
  return (
    <footer className="bg-secondary text-secondary-foreground py-12 px-6 md:px-12 lg:px-20">
      <div className="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
        <img src={logo} alt="360 Video Marketers" className="h-10" />
        <p className="text-secondary-foreground/60 text-sm">
          © {new Date().getFullYear()} 360 Videomarketers. All rights reserved.
        </p>
      </div>
    </footer>
  );
};

export default Footer;
