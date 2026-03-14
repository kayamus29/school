import { ArrowRight, Play } from "lucide-react";
import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";
import heroImage from "@/assets/hero_main.jpg";

const HeroSection = () => {
  return (
    <section className="relative min-h-[90vh] flex items-center overflow-hidden">
      {/* Background Image with Overlay */}
      <div className="absolute inset-0 z-0">
        <img
          src={heroImage}
          alt="Students engaged in learning at MIENEBI International School in Gwarimpa Estate, Abuja"
          className="w-full h-full object-cover"
        />
        <div className="absolute inset-0 bg-gradient-to-r from-primary/95 via-primary/80 to-primary/60" />
        <div className="absolute inset-0 bg-gradient-to-t from-primary/50 to-transparent" />
      </div>

      {/* Decorative Elements */}
      <div className="absolute top-20 right-20 w-72 h-72 bg-secondary/20 rounded-full blur-3xl animate-pulse-slow hidden lg:block" />
      <div className="absolute bottom-20 left-20 w-96 h-96 bg-secondary/10 rounded-full blur-3xl animate-float hidden lg:block" />

      <div className="container relative z-10 py-16 sm:py-20">
        <div className="max-w-3xl">
          {/* Badge */}
          <div className="inline-flex items-center gap-2 bg-secondary/20 backdrop-blur-sm text-primary-foreground px-4 py-2 rounded-full mb-6 animate-fade-in-down">
            <span className="w-2 h-2 bg-secondary rounded-full animate-pulse" />
            <span className="text-sm font-medium">Admissions Open for MIENEBI International School 2024/2025</span>
          </div>

          {/* Main Heading */}
          <h1 className="font-serif text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold text-primary-foreground leading-tight mb-6 animate-fade-in-up">
            MIENEBI International School: Offering British and Nigerian Curriculum in Abuja
          </h1>

          {/* Subtitle */}
          <p className="text-lg sm:text-xl text-primary-foreground/80 leading-relaxed mb-8 max-w-2xl animate-fade-in-up stagger-2">
            We offer quality education at all levels in Gwarimpa Estate, FCT Abuja.
          </p>

          {/* CTA Buttons */}
          <div className="flex flex-col sm:flex-row gap-4 animate-fade-in-up stagger-3">
            <Button
              asChild
              size="lg"
              className="bg-secondary hover:bg-secondary/90 text-primary font-bold text-lg px-8 shadow-gold group"
            >
              <Link to="/admissions">
                Apply for Admission to MIENEBI International School
                <ArrowRight className="ml-2 h-5 w-5 group-hover:translate-x-1 transition-transform" />
              </Link>
            </Button>
            // Removed secondary outline button linking to /about
          </div>

          {/* Quick Stats */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mt-12 sm:mt-16 pt-6 sm:pt-8 border-t border-primary-foreground/20 animate-fade-in-up stagger-4">
            {[
              { value: "39+", label: "Years of Excellence" },
              { value: "2,500+", label: "Students Enrolled" },
              { value: "150+", label: "Expert Teachers" },
              { value: "98%", label: "Success Rate" },
            ].map((stat, index) => (
              <div key={index} className="text-center">
                <div className="font-serif text-3xl sm:text-4xl font-bold text-secondary mb-1">
                  {stat.value}
                </div>
                <div className="text-sm text-primary-foreground/70">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Scroll Indicator */}
      <div className="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce hidden md:block">
        <div className="w-8 h-12 border-2 border-primary-foreground/30 rounded-full flex justify-center pt-2">
          <div className="w-1.5 h-3 bg-primary-foreground/50 rounded-full animate-pulse" />
        </div>
      </div>
    </section>
  );
};

export default HeroSection;
