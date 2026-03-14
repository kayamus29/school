import { Link } from "react-router-dom";
import {
  GraduationCap,
  MapPin,
  Phone,
  Mail,
  Clock,
  Facebook,
  Twitter,
  Instagram,
  Youtube,
  ArrowRight,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

const Footer = () => {
  const quickLinks = [
    { label: "Home", path: "/" },
    { label: "About Us", path: "/about" },
    { label: "Admissions", path: "/admissions" },
    { label: "Blog", path: "/news" },
    { label: "Contact Us", path: "/contact" },
  ];

  const usefulLinks = [
    { label: "Home", path: "/" },
    { label: "About Us", path: "/about" },
    { label: "Admissions", path: "/admissions" },
    { label: "Blog", path: "/news" },
    { label: "Contact Us", path: "/contact" },
  ];

  const socialLinks = [
    { icon: Facebook, href: "https://www.facebook.com/Mienebischool", label: "Facebook" },
    { icon: Instagram, href: "https://www.instagram.com/Mienebisch", label: "Instagram" },
  ];

  return (
    <footer className="bg-primary text-primary-foreground">
      {/* Newsletter Section */}
      <div className="border-b border-primary-foreground/10">
        <div className="container py-12">
          <div className="flex flex-col lg:flex-row items-center justify-between gap-6">
            <div className="text-center lg:text-left">
              <h3 className="font-serif text-2xl font-bold mb-2">
                Subscribe to Our Newsletter
              </h3>
              <p className="text-primary-foreground/70">
                Stay updated with the latest news, events, and announcements.
              </p>
            </div>
            <div className="flex gap-3 w-full max-w-md">
              <Input
                type="email"
                placeholder="Enter your email"
                className="bg-primary-foreground/10 border-primary-foreground/20 text-primary-foreground placeholder:text-primary-foreground/50 focus-visible:ring-secondary"
              />
              <Button className="bg-secondary hover:bg-secondary/90 text-secondary-foreground font-semibold shrink-0">
                Subscribe
                <ArrowRight className="ml-2 h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
      </div>

      {/* Main Footer */}
      <div className="container py-16">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
          {/* About */}
          <div>
            <Link to="/" className="flex items-center gap-3 mb-6">
              <div className="w-12 h-12 rounded-full bg-primary-foreground/10 flex items-center justify-center">
                <GraduationCap className="h-7 w-7 text-secondary" />
              </div>
              <div>
                <h2 className="font-serif text-xl font-bold">MIENEBI International School</h2>
                <p className="text-xs text-primary-foreground/70 tracking-wider uppercase">
                  The Future Leaders Leading the Way
                </p>
              </div>
            </Link>
            <p className="text-primary-foreground/70 mb-6 leading-relaxed">
              We Offer British and Nigerian Curriculum. We offer quality education at all levels.
            </p>
            <div className="flex gap-3">
              {socialLinks.map((social) => (
                <a
                  key={social.label}
                  href={social.href}
                  aria-label={social.label}
                  className="w-10 h-10 rounded-full bg-primary-foreground/10 flex items-center justify-center hover:bg-secondary hover:text-secondary-foreground transition-all duration-300"
                >
                  <social.icon className="h-5 w-5" />
                </a>
              ))}
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="font-serif text-lg font-bold mb-6 relative">
              Quick Links
              <span className="absolute bottom-0 left-0 w-12 h-0.5 bg-secondary -mb-2" />
            </h3>
            <ul className="space-y-3">
              {quickLinks.map((link) => (
                <li key={link.path}>
                  <Link
                    to={link.path}
                    className="text-primary-foreground/70 hover:text-secondary transition-colors flex items-center gap-2 group"
                  >
                    <ArrowRight className="h-4 w-4 opacity-0 -ml-6 group-hover:opacity-100 group-hover:ml-0 transition-all duration-300" />
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Useful Links */}
          <div>
            <h3 className="font-serif text-lg font-bold mb-6 relative">
              Useful Links
              <span className="absolute bottom-0 left-0 w-12 h-0.5 bg-secondary -mb-2" />
            </h3>
            <ul className="space-y-3">
              {usefulLinks.map((link) => (
                <li key={link.path}>
                  <Link
                    to={link.path}
                    className="text-primary-foreground/70 hover:text-secondary transition-colors flex items-center gap-2 group"
                  >
                    <ArrowRight className="h-4 w-4 opacity-0 -ml-6 group-hover:opacity-100 group-hover:ml-0 transition-all duration-300" />
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact Info */}
          <div>
            <h3 className="font-serif text-lg font-bold mb-6 relative">
              Contact Us
              <span className="absolute bottom-0 left-0 w-12 h-0.5 bg-secondary -mb-2" />
            </h3>
            <ul className="space-y-4">
              <li className="flex gap-3">
                <MapPin className="h-5 w-5 text-secondary shrink-0 mt-0.5" />
                <span className="text-primary-foreground/70">
                  Ps. Egure Street, Off Wole Soyinka Street, 2nd Avenue, Behind Glass House, Gwarimpa Estate, FCT Abuja
                </span>
              </li>
              <li className="flex gap-3">
                <Phone className="h-5 w-5 text-secondary shrink-0" />
                <a
                  href="tel:08035983003"
                  className="text-primary-foreground/70 hover:text-secondary transition-colors"
                >
                  08035983003
                </a>
              </li>
              <li className="flex gap-3">
                <Phone className="h-5 w-5 text-secondary shrink-0" />
                <a
                  href="tel:09095201652"
                  className="text-primary-foreground/70 hover:text-secondary transition-colors"
                >
                  09095201652
                </a>
              </li>
              <li className="flex gap-3">
                <Mail className="h-5 w-5 text-secondary shrink-0" />
                <a
                  href="mailto:mienebisch@gmail.com"
                  className="text-primary-foreground/70 hover:text-secondary transition-colors"
                >
                  mienebisch@gmail.com
                </a>
              </li>
              <li className="flex gap-3">
                <Clock className="h-5 w-5 text-secondary shrink-0" />
                <span className="text-primary-foreground/70">
                  Mon - Fri: 8:00 AM - 4:00 PM
                </span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      {/* Bottom Bar */}
      <div className="border-t border-primary-foreground/10">
        <div className="container py-6 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-primary-foreground/60">
          <p>
            © {new Date().getFullYear()} MIENEBI International School. All rights reserved.
          </p>
          <div className="flex gap-6">
            <Link to="/privacy" className="hover:text-secondary transition-colors">
              Privacy Policy
            </Link>
            <Link to="/terms" className="hover:text-secondary transition-colors">
              Terms of Use
            </Link>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
