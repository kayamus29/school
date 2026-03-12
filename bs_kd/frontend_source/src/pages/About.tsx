import Layout from "@/components/layout/Layout";
import { Link } from "react-router-dom";
import { ArrowRight, Target, Eye, Heart, Award, Users, BookOpen } from "lucide-react";
import { Button } from "@/components/ui/button";
import aboutImage from "@/assets/about_story.jpg";
import principalImage from "@/assets/staff_principal.jpg";
import { Helmet } from "react-helmet-async";

const values = [
  {
    icon: Target,
    title: "Excellence",
    description: "We strive for the highest standards in everything we do, encouraging our students to reach their full potential.",
  },
  {
    icon: Heart,
    title: "Integrity",
    description: "We foster honesty, transparency, and ethical behavior in all our interactions and decisions.",
  },
  {
    icon: Users,
    title: "Community",
    description: "We build strong relationships and a sense of belonging among students, families, and staff.",
  },
  {
    icon: BookOpen,
    title: "Innovation",
    description: "We embrace new ideas and approaches to enhance learning and prepare students for the future.",
  },
];

const About = () => {
  return (
    <Layout>
      <Helmet>
        <title>About Us | MIENEBI International School</title>
        <meta name="description" content="Discover MIENEBI International School: vision, mission, leadership, and world-class facilities in Abuja." />
        <meta property="og:title" content="About Us | MIENEBI International School" />
      </Helmet>
      {/* Hero Section */}
      <section className="bg-primary text-primary-foreground py-20 lg:py-28 relative overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          <div className="absolute top-0 left-0 w-full h-full" style={{
            backgroundImage: `radial-gradient(circle at 2px 2px, currentColor 1px, transparent 0)`,
            backgroundSize: '40px 40px'
          }} />
        </div>
        <div className="container relative">
          <div className="max-w-3xl">
            <div className="inline-flex items-center gap-2 bg-secondary/20 text-primary-foreground px-4 py-2 rounded-full mb-6">
              <span className="text-sm font-medium">About Us</span>
            </div>
            <h1 className="font-serif text-4xl lg:text-5xl xl:text-6xl font-bold mb-6">
              Shaping Futures Since 1985
            </h1>
            <p className="text-lg text-primary-foreground/80 leading-relaxed">
              For over three decades, Bright Horizon Academy has been a beacon of
              educational excellence, nurturing young minds and building tomorrow's
              leaders.
            </p>
          </div>
        </div>
      </section>

      {/* Mission & Vision */}
      <section className="py-20 lg:py-28 bg-background">
        <div className="container">
          <div className="grid lg:grid-cols-2 gap-16 items-center">
            <div>
              <img
                src={aboutImage}
                alt="Students engaged in classroom learning"
                className="w-full rounded-2xl shadow-elegant"
              />
            </div>
            <div>
              <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-8">
                Our Story
              </h2>
              <p className="text-muted-foreground leading-relaxed mb-6">
                Founded in 1985, Bright Horizon Academy began as a small school
                with a big dream: to provide quality education that prepares
                students not just for exams, but for life. What started with 50
                students and 5 teachers has grown into a thriving community of
                over 2,500 students and 150+ dedicated educators.
              </p>
              <p className="text-muted-foreground leading-relaxed mb-8">
                Today, we stand as one of the region's most respected educational
                institutions, known for our innovative teaching methods,
                state-of-the-art facilities, and most importantly, our commitment
                to developing well-rounded individuals who contribute positively
                to society.
              </p>

              <div className="grid sm:grid-cols-2 gap-6">
                <div className="bg-accent rounded-xl p-6">
                  <div className="flex items-center gap-3 mb-3">
                    <Eye className="h-6 w-6 text-secondary" />
                    <h3 className="font-serif text-xl font-bold text-primary">Our Vision</h3>
                  </div>
                  <p className="text-muted-foreground text-sm">
                    To be a world-class institution that inspires lifelong learning
                    and empowers students to become responsible global citizens.
                  </p>
                </div>
                <div className="bg-accent rounded-xl p-6">
                  <div className="flex items-center gap-3 mb-3">
                    <Target className="h-6 w-6 text-secondary" />
                    <h3 className="font-serif text-xl font-bold text-primary">Our Mission</h3>
                  </div>
                  <p className="text-muted-foreground text-sm">
                    To provide holistic education that nurtures academic excellence,
                    character development, and creative thinking in every student.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Core Values */}
      <section className="py-20 lg:py-28 bg-muted/50">
        <div className="container">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-4">
              Our Core Values
            </h2>
            <p className="text-muted-foreground text-lg">
              The principles that guide our approach to education and shape our
              school community.
            </p>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {values.map((value, index) => (
              <div
                key={index}
                className="bg-card rounded-2xl p-8 text-center card-hover shadow-card"
              >
                <div className="w-16 h-16 mx-auto mb-6 rounded-2xl bg-secondary/10 flex items-center justify-center group-hover:bg-secondary transition-colors">
                  <value.icon className="h-8 w-8 text-secondary" />
                </div>
                <h3 className="font-serif text-xl font-bold text-primary mb-3">
                  {value.title}
                </h3>
                <p className="text-muted-foreground text-sm leading-relaxed">
                  {value.description}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Principal's Message */}
      <section className="py-20 lg:py-28 bg-background">
        <div className="container">
          <div className="grid lg:grid-cols-2 gap-16 items-center">
            <div className="order-2 lg:order-1">
              <div className="inline-flex items-center gap-2 bg-secondary/10 text-secondary-foreground px-4 py-2 rounded-full mb-6">
                <span className="text-sm font-medium">Leadership</span>
              </div>
              <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-6">
                Message from the Principal
              </h2>
              <p className="text-muted-foreground leading-relaxed mb-6 italic text-lg">
                "Education is the most powerful weapon which you can use to change
                the world. At Bright Horizon Academy, we are committed to providing
                that weapon to every child who walks through our doors."
              </p>
              <p className="text-muted-foreground leading-relaxed mb-6">
                Our approach to education goes beyond textbooks. We focus on
                developing critical thinking, creativity, and character. Every
                student is unique, and we strive to recognize and nurture their
                individual talents while preparing them for the challenges of
                tomorrow.
              </p>
              <p className="text-muted-foreground leading-relaxed mb-8">
                I invite you to visit our campus, meet our dedicated faculty, and
                see firsthand the difference a Bright Horizon education can make.
              </p>
              <div className="flex items-center gap-4">
                <div className="w-16 h-0.5 bg-secondary" />
                <div>
                  <div className="font-serif text-xl font-bold text-primary">
                    Dr. Sarah Mitchell
                  </div>
                  <div className="text-sm text-muted-foreground">
                    Principal, Bright Horizon Academy
                  </div>
                </div>
              </div>
            </div>
            <div className="order-1 lg:order-2">
              <div className="relative">
                <img
                  src={principalImage}
                  alt="Dr. Sarah Mitchell, Principal"
                  className="w-full max-w-md mx-auto rounded-2xl shadow-elegant"
                />
                <div className="absolute -bottom-6 -left-6 bg-secondary text-secondary-foreground p-6 rounded-2xl shadow-gold hidden sm:block">
                  <div className="font-serif text-3xl font-bold">25+</div>
                  <div className="text-sm font-medium">Years in Education</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Accreditations */}
      <section className="py-20 lg:py-28 bg-primary text-primary-foreground">
        <div className="container">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <h2 className="font-serif text-3xl lg:text-4xl font-bold mb-4">
              Accreditations & Affiliations
            </h2>
            <p className="text-primary-foreground/70">
              Recognized for excellence by leading educational bodies.
            </p>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {[
              { icon: Award, label: "CBSE Affiliated", desc: "Central Board of Secondary Education" },
              { icon: Award, label: "ISO 9001:2015", desc: "Quality Management Certified" },
              { icon: Award, label: "Green School", desc: "Environmental Excellence Award" },
              { icon: Award, label: "Digital Campus", desc: "Smart School Certification" },
            ].map((item, index) => (
              <div key={index} className="text-center">
                <div className="w-16 h-16 mx-auto mb-4 rounded-2xl bg-secondary/20 flex items-center justify-center">
                  <item.icon className="h-8 w-8 text-secondary" />
                </div>
                <h3 className="font-serif text-lg font-bold mb-1">{item.label}</h3>
                <p className="text-primary-foreground/60 text-sm">{item.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="py-20 lg:py-28 bg-background">
        <div className="container">
          <div className="text-center max-w-2xl mx-auto">
            <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-6">
              Ready to Join Our Community?
            </h2>
            <p className="text-muted-foreground text-lg mb-8">
              Take the first step towards a brighter future for your child.
              Schedule a campus visit or apply for admission today.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button asChild size="lg" className="bg-secondary hover:bg-secondary/90 text-secondary-foreground font-semibold shadow-gold">
                <Link to="/admissions">
                  Apply Now
                  <ArrowRight className="ml-2 h-5 w-5" />
                </Link>
              </Button>
              <Button asChild size="lg" variant="outline" className="border-2 border-primary text-primary hover:bg-primary hover:text-primary-foreground font-semibold">
                <Link to="/contact">
                  Schedule a Visit
                </Link>
              </Button>
            </div>
          </div>
        </div>
      </section>
    </Layout>
  );
};

export default About;
