import { useEffect, useRef, useState } from "react";
import { GraduationCap, Users, Trophy, BookOpen } from "lucide-react";

interface StatItemProps {
  icon: React.ElementType;
  value: number;
  suffix?: string;
  label: string;
  delay?: number;
}

const StatItem = ({ icon: Icon, value, suffix = "", label, delay = 0 }: StatItemProps) => {
  const [count, setCount] = useState(0);
  const [isVisible, setIsVisible] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsVisible(true);
        }
      },
      { threshold: 0.3 }
    );

    if (ref.current) {
      observer.observe(ref.current);
    }

    return () => observer.disconnect();
  }, []);

  useEffect(() => {
    if (!isVisible) return;

    const duration = 2000;
    const steps = 60;
    const stepValue = value / steps;
    let current = 0;

    const timer = setTimeout(() => {
      const interval = setInterval(() => {
        current += stepValue;
        if (current >= value) {
          setCount(value);
          clearInterval(interval);
        } else {
          setCount(Math.floor(current));
        }
      }, duration / steps);
    }, delay);

    return () => clearTimeout(timer);
  }, [isVisible, value, delay]);

  return (
    <div ref={ref} className="text-center group">
      <div className="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 rounded-2xl bg-secondary/10 flex items-center justify-center group-hover:bg-secondary group-hover:scale-110 transition-all duration-300">
        <Icon className="h-8 w-8 sm:h-10 sm:w-10 text-secondary group-hover:text-secondary-foreground transition-colors" />
      </div>
      <div className="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-primary mb-2">
        {count.toLocaleString()}
        {suffix}
      </div>
      <div className="text-muted-foreground font-medium">{label}</div>
    </div>
  );
};

const StatsSection = () => {
  const stats = [
    { icon: Users, value: 2500, suffix: "+", label: "Students Enrolled at MIENEBI" },
    { icon: GraduationCap, value: 150, suffix: "+", label: "Dedicated Teachers" },
    { icon: Trophy, value: 98, suffix: "%", label: "Examination Success Rate" },
    { icon: BookOpen, value: 39, suffix: "+", label: "Years of Educational Excellence" },
  ];

  return (
    <section className="py-14 sm:py-20 bg-card relative overflow-hidden">
      {/* Decorative Pattern */}
      <div className="absolute inset-0 opacity-5">
        <div className="absolute top-0 left-0 w-full h-full" style={{
          backgroundImage: `radial-gradient(circle at 2px 2px, currentColor 1px, transparent 0)`,
          backgroundSize: '40px 40px'
        }} />
      </div>

      <div className="container relative">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-12">
          {stats.map((stat, index) => (
            <StatItem key={index} {...stat} delay={index * 200} />
          ))}
        </div>
      </div>
    </section>
  );
};

export default StatsSection;
