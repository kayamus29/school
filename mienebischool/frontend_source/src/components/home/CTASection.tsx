import { ArrowRight, FileText, Phone } from "lucide-react";
import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";

const CTASection = () => {
  return (
    <section className="py-20 lg:py-28 bg-background relative overflow-hidden">
      <div className="container">
        <div className="bg-gradient-to-br from-primary via-primary to-navy-dark rounded-3xl p-8 lg:p-16 relative overflow-hidden">
          {/* Decorative Elements */}
          <div className="absolute top-0 right-0 w-64 h-64 bg-secondary/20 rounded-full blur-3xl" />
          <div className="absolute bottom-0 left-0 w-96 h-96 bg-secondary/10 rounded-full blur-3xl" />

          <div className="relative z-10 text-center max-w-3xl mx-auto">
            <h2 className="font-serif text-3xl lg:text-4xl xl:text-5xl font-bold text-primary-foreground mb-6">
              Begin Your Child's Journey to{" "}
              <span className="text-secondary">Excellence</span> at MIENEBI International School
            </h2>
            <p className="text-lg text-primary-foreground/80 mb-10 leading-relaxed">
              Admissions are now open for the 2024/2025 academic year. Give your
              child the gift of quality British and Nigerian education at MIENEBI International School in Gwarimpa Estate, FCT Abuja. Join
              our community of learners, leaders, and innovators.
            </p>

            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button
                asChild
                size="lg"
                className="bg-secondary hover:bg-secondary/90 text-secondary-foreground font-semibold text-lg px-8 shadow-gold group"
              >
                <Link to="/admissions">
                  <FileText className="mr-2 h-5 w-5" />
                  Apply Online to MIENEBI International School
                  <ArrowRight className="ml-2 h-5 w-5 group-hover:translate-x-1 transition-transform" />
                </Link>
              </Button>
              <Button
                asChild
                size="lg"
                className="bg-secondary hover:bg-secondary/90 text-primary font-bold text-lg px-8 shadow-gold group"
              >
                <a href="tel:08035983003">
                  <Phone className="mr-2 h-5 w-5" />
                  Call MIENEBI Admissions: 08035983003
                </a>
              </Button>
            </div>

            {/* Trust Badges */}
            <div className="mt-12 pt-8 border-t border-primary-foreground/20 flex flex-wrap justify-center gap-8">
              {[
                "WAEC and NECO Approved",
                "ISO 9001:2015",
                "Green School Award",
                "Digital Campus",
              ].map((badge) => (
                <div
                  key={badge}
                  className="flex items-center gap-2 text-primary-foreground/70"
                >
                  <div className="w-2 h-2 bg-secondary rounded-full" />
                  <span className="text-sm font-medium">{badge}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default CTASection;
