import { useState, useEffect } from "react";
import { Link, useLocation } from "react-router-dom";
import { Menu, X, ChevronDown, Phone, Mail } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
  NavigationMenu,
  NavigationMenuContent,
  NavigationMenuItem,
  NavigationMenuLink,
  NavigationMenuList,
  NavigationMenuTrigger,
} from "@/components/ui/navigation-menu";
import { cn } from "@/lib/utils";
import { MenuToggleIcon } from "@/components/ui/menu-toggle-icon";

const Header = () => {
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const location = useLocation();

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 20);
    };
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  // Manage focus when mobile menu opens/closes
  useEffect(() => {
    if (isMobileMenuOpen) {
      const firstLink = document.querySelector('#mobile-menu a') as HTMLElement | null;
      firstLink?.focus();
    }
  }, [isMobileMenuOpen]);

  // Close mobile menu on route change for consistency
  useEffect(() => {
    setIsMobileMenuOpen(false);
  }, [location.pathname]);

  const navItems = [
    { label: "Home", path: "/" },
    { label: "About Us", path: "/about" },
    { label: "Admissions", path: "/admissions" },
    { label: "Blog", path: "/news" },
    { label: "Services", path: "/services" },
    { label: "Career", path: "/career" },
    { label: "Contact", path: "/contact" },
  ];

  const isActive = (path: string) => location.pathname === path;

  return (
    <>
      {/* Top Bar */}
      <div className="bg-primary text-primary-foreground py-2 hidden md:block">
        <div className="container flex justify-between items-center text-sm">
          <div className="flex items-center gap-6">
            <a href="tel:08035983003" className="flex items-center gap-2 hover:text-secondary transition-colors">
              <Phone className="h-4 w-4" />
              <span>08035983003</span>
            </a>
            <a href="mailto:mienebisch@gmail.com" className="flex items-center gap-2 hover:text-secondary transition-colors">
              <Mail className="h-4 w-4" />
              <span>mienebisch@gmail.com</span>
            </a>
          </div>
          <div className="flex items-center gap-4">
            <a href="/login" className="hover:text-secondary transition-colors">
              Portal Login
            </a>
          </div>
        </div>
      </div>

      {/* Main Navigation */}
      <header
        className={cn(
          "sticky top-0 z-50 w-full transition-all duration-300",
          isScrolled
            ? "bg-card/95 backdrop-blur-md shadow-elegant py-2"
            : "bg-card py-4"
        )}
      >
        <div className="container flex items-center justify-between">
          {/* Logo */}
          <Link to="/" className="flex items-center gap-3 group">
            <div className="relative">
              <div className="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center group-hover:scale-105 transition-transform overflow-hidden ring-1 ring-primary/30">
                <img src="/logo.png" alt="MIENEBI International School Logo" className="h-12 w-12 object-cover" />
              </div>
              <div className="absolute -bottom-1 -right-1 w-4 h-4 bg-secondary rounded-full" />
            </div>
            <div className="block">
              <h1 className="font-serif text-base sm:text-xl font-bold text-primary leading-tight">
                MIENEBI International School
              </h1>
              <p className="hidden md:block text-xs text-muted-foreground font-medium tracking-wider uppercase">
                The Future Leaders Leading the Way
              </p>
            </div>
          </Link>

          {/* Desktop Navigation */}
          <nav className="hidden lg:flex items-center gap-1">
            {navItems.map((item) => (
              <Link
                key={item.path}
                to={item.path}
                className={cn(
                  "px-4 py-2 text-sm font-medium rounded-md transition-all duration-200",
                  isActive(item.path)
                    ? "text-primary bg-accent"
                    : "text-muted-foreground hover:text-primary hover:bg-accent/50"
                )}
              >
                {item.label}
              </Link>
            ))}
          </nav>

          {/* CTA Button */}
          <div className="hidden lg:flex items-center gap-4">
            <Button
              asChild
              className="bg-secondary hover:bg-secondary/90 text-secondary-foreground font-semibold shadow-gold"
            >
              <Link to="/login">Login</Link>
            </Button>
          </div>

          {/* Mobile Menu Button */}
          <button
            type="button"
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            className="lg:hidden p-2 rounded-md hover:bg-accent transition-colors"
            aria-label="Toggle navigation menu"
            aria-expanded={isMobileMenuOpen}
            aria-controls="mobile-menu"
            aria-haspopup="menu"
          >
            <MenuToggleIcon open={isMobileMenuOpen} className="h-6 w-6 text-primary" duration={300} />
          </button>
        </div>

        {/* Mobile Menu */}
        <div
          className={cn(
            "lg:hidden absolute top-full left-0 right-0 bg-card border-t shadow-lg transition-all duration-300 overflow-hidden",
            isMobileMenuOpen ? "max-h-[500px] opacity-100" : "max-h-0 opacity-0"
          )}
          id="mobile-menu"
          role="navigation"
          aria-label="Mobile"
          onKeyDown={(e) => {
            if (e.key === 'Escape') setIsMobileMenuOpen(false);
          }}
        >
          <nav className="container py-4 flex flex-col gap-1">
            {navItems.map((item) => (
              <Link
                key={item.path}
                to={item.path}
                onClick={() => setIsMobileMenuOpen(false)}
                className={cn(
                  "px-4 py-3 text-sm font-medium rounded-md transition-colors",
                  isActive(item.path)
                    ? "text-primary bg-accent"
                    : "text-muted-foreground hover:text-primary hover:bg-accent/50"
                )}
              >
                {item.label}
              </Link>
            ))}
            <div className="mt-4 pt-4 border-t">
              <Button
                asChild
                className="w-full bg-secondary hover:bg-secondary/90 text-secondary-foreground font-semibold"
              >
                <Link to="/login" onClick={() => setIsMobileMenuOpen(false)}>
                  Login
                </Link>
              </Button>
            </div>
          </nav>
        </div>
      </header>
    </>
  );
};

export default Header;
