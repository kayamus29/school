import { useState, useEffect } from "react";
import { Quote, ChevronLeft, ChevronRight, Star } from "lucide-react";
import { Button } from "@/components/ui/button";

const testimonials = [
  {
    id: 1,
    name: "Jennifer Thompson",
    role: "Parent of 8th Grader",
    image: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&h=150&fit=crop&crop=face",
    content:
      "Bright Horizon Academy has been transformative for my daughter. The teachers are incredibly dedicated, and the curriculum challenges students while supporting their growth. We've seen remarkable improvement in both academics and confidence.",
    rating: 5,
  },
  {
    id: 2,
    name: "Michael Chen",
    role: "Alumni, Class of 2020",
    image: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face",
    content:
      "The foundation I received at Bright Horizon prepared me exceptionally well for university. The critical thinking skills, work ethic, and values I developed here continue to guide my success in higher education.",
    rating: 5,
  },
  {
    id: 3,
    name: "Sarah Williams",
    role: "Parent of 5th Grader",
    image: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face",
    content:
      "What sets this school apart is the genuine care for each student's well-being. The communication with parents is excellent, and the extracurricular activities help develop well-rounded individuals.",
    rating: 5,
  },
  {
    id: 4,
    name: "David Rodriguez",
    role: "Parent of 10th Grader",
    image: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face",
    content:
      "We moved here specifically for Bright Horizon Academy, and it was the best decision for our family. The college preparation, career guidance, and supportive environment have been invaluable for our son's future.",
    rating: 5,
  },
];

const TestimonialsSection = () => {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [isAutoPlaying, setIsAutoPlaying] = useState(true);

  useEffect(() => {
    if (!isAutoPlaying) return;
    const timer = setInterval(() => {
      setCurrentIndex((prev) => (prev + 1) % testimonials.length);
    }, 5000);
    return () => clearInterval(timer);
  }, [isAutoPlaying]);

  const goToPrev = () => {
    setIsAutoPlaying(false);
    setCurrentIndex((prev) => (prev - 1 + testimonials.length) % testimonials.length);
  };

  const goToNext = () => {
    setIsAutoPlaying(false);
    setCurrentIndex((prev) => (prev + 1) % testimonials.length);
  };

  const currentTestimonial = testimonials[currentIndex];

  return (
    <section className="py-14 sm:py-20 lg:py-28 bg-muted/50 relative overflow-hidden">
      {/* Decorative */}
      <div className="absolute top-0 left-1/2 -translate-x-1/2 w-72 sm:w-96 h-72 sm:h-96 bg-secondary/10 rounded-full blur-3xl" />

      <div className="container relative">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
          <div className="inline-flex items-center gap-2 bg-secondary/10 text-secondary-foreground px-4 py-2 rounded-full mb-4">
            <span className="text-sm font-medium">Testimonials</span>
          </div>
          <h2 className="font-serif text-3xl lg:text-4xl xl:text-5xl font-bold text-primary mb-4">
            What Our Community Says About MIENEBI International School
          </h2>
          <p className="text-muted-foreground text-lg">
            Hear from parents, students, and alumni about their experiences at
            MIENEBI International School.
          </p>
        </div>

        {/* Testimonial Card */}
        <div className="max-w-4xl mx-auto">
          <div className="bg-card rounded-3xl p-6 sm:p-8 lg:p-12 shadow-elegant relative">
            <Quote className="absolute top-6 sm:top-8 right-6 sm:right-8 h-16 sm:h-24 w-16 sm:w-24 text-secondary/10" />
            
            <div className="relative z-10">
              {/* Stars */}
              <div className="flex gap-1 mb-4 sm:mb-6">
                {[...Array(currentTestimonial.rating)].map((_, i) => (
                  <Star key={i} className="h-4 w-4 sm:h-5 sm:w-5 fill-secondary text-secondary" />
                ))}
              </div>

              {/* Content */}
              <p className="text-base sm:text-lg lg:text-xl text-muted-foreground leading-relaxed mb-6 sm:mb-8 italic">
                "{currentTestimonial.content.replace("Bright Horizon Academy", "MIENEBI International School")}"
              </p>

              {/* Author */}
              <div className="flex items-center gap-3 sm:gap-4">
                <img
                  src={currentTestimonial.image}
                  alt={`${currentTestimonial.name}, ${currentTestimonial.role} at MIENEBI International School`}
                  className="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-2 border-secondary"
                />
                <div>
                  <div className="font-serif text-lg sm:text-xl font-bold text-primary">
                    {currentTestimonial.name}
                  </div>
                  <div className="text-muted-foreground">
                    {currentTestimonial.role}
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Navigation */}
          <div className="flex items-center justify-center gap-4 mt-8">
            <Button
              variant="outline"
              size="icon"
              onClick={goToPrev}
              className="rounded-full border-2 border-primary/20 hover:border-primary hover:bg-primary hover:text-primary-foreground"
            >
              <ChevronLeft className="h-5 w-5" />
            </Button>

            {/* Dots */}
            <div className="flex gap-2">
              {testimonials.map((_, index) => (
                <button
                  key={index}
                  onClick={() => {
                    setIsAutoPlaying(false);
                    setCurrentIndex(index);
                  }}
                  className={`w-2.5 h-2.5 rounded-full transition-all duration-300 ${
                    index === currentIndex
                      ? "bg-secondary w-8"
                      : "bg-primary/20 hover:bg-primary/40"
                  }`}
                />
              ))}
            </div>

            <Button
              variant="outline"
              size="icon"
              onClick={goToNext}
              className="rounded-full border-2 border-primary/20 hover:border-primary hover:bg-primary hover:text-primary-foreground"
            >
              <ChevronRight className="h-5 w-5" />
            </Button>
          </div>
        </div>
      </div>
    </section>
  );
};

export default TestimonialsSection;
