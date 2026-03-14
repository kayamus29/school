import { Quote } from "lucide-react";
import principalImage from "@/assets/staff_principal.jpg";

const WelcomeSection = () => {
  return (
    <section className="py-20 lg:py-28 bg-background">
      <div className="container">
        <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
          {/* Image Side */}
          <div className="relative order-2 lg:order-1">
            <div className="relative z-10">
              <img
                src={principalImage}
                alt="Proprietor at MIENEBI International School in Gwarimpa Estate, Abuja"
                className="w-full max-w-md mx-auto lg:mx-0 rounded-2xl shadow-elegant"
              />
              {/* Experience Badge */}
              <div className="absolute -bottom-6 -right-6 lg:right-auto lg:-left-6 bg-secondary text-secondary-foreground p-6 rounded-2xl shadow-gold">
                <div className="font-serif text-3xl font-bold">25+</div>
                <div className="text-sm font-medium">Years in Education</div>
              </div>
            </div>
            {/* Decorative Elements */}
            <div className="absolute top-8 -left-4 w-24 h-24 bg-primary/10 rounded-full -z-10" />
            <div className="absolute -bottom-8 right-8 w-32 h-32 bg-secondary/20 rounded-full -z-10" />
          </div>

          {/* Content Side */}
          <div className="order-1 lg:order-2">
            <div className="inline-flex items-center gap-2 bg-accent text-accent-foreground px-4 py-2 rounded-full mb-6">
              <span className="text-sm font-medium">Proprietor's Message</span>
            </div>

            <h2 className="font-serif text-3xl lg:text-4xl xl:text-5xl font-bold text-primary mb-6 leading-tight">
              Welcome to MIENEBI International School
            </h2>

            <div className="relative mb-6">
              <Quote className="absolute -top-4 -left-4 h-12 w-12 text-secondary/30" />
              <p className="text-lg text-muted-foreground leading-relaxed pl-8 italic">
                Welcome to the 2024/2025 academic session at MIENEBI International School! This year, we are placing a special emphasis on practical learning to ensure our students are not just learning, but are also able to apply their knowledge in real-world scenarios, preparing them to be future leaders.
              </p>
            </div>

            <p className="text-muted-foreground leading-relaxed mb-6">
              Our dedicated faculty, state-of-the-art facilities in Gwarimpa Estate, FCT Abuja, and innovative British and Nigerian curriculum work together to provide an environment where every student can thrive. We believe in developing not just academic excellence, but also the social, emotional, and physical well-being of our students.
            </p>

            <p className="text-muted-foreground leading-relaxed mb-8">
              Together with our parents and community, we strive to prepare our
              students to become responsible global citizens, ready to face the
              challenges of the 21st century with confidence and integrity.
            </p>

            <div className="flex items-center gap-4">
              <div className="w-16 h-0.5 bg-secondary" />
              <div>
                <div className="font-serif text-xl font-bold text-primary">
                  Proprietor
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default WelcomeSection;
