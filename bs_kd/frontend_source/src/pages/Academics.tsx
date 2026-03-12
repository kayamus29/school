import Layout from "@/components/layout/Layout";
import { Link } from "react-router-dom";
import { ArrowRight, BookOpen, GraduationCap, Trophy, Calendar, Download } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Helmet } from "react-helmet-async";

const grades = [
  { level: "Pre-Primary", classes: ["Nursery", "LKG", "UKG"], ages: "3-5 years" },
  { level: "Primary", classes: ["Grade 1", "Grade 2", "Grade 3", "Grade 4", "Grade 5"], ages: "6-10 years" },
  { level: "Middle School", classes: ["Grade 6", "Grade 7", "Grade 8"], ages: "11-13 years" },
  { level: "High School", classes: ["Grade 9", "Grade 10"], ages: "14-15 years" },
  { level: "Senior Secondary", classes: ["Grade 11", "Grade 12"], ages: "16-17 years" },
];

const subjects = {
  core: ["English", "Mathematics", "Science", "Social Studies", "Second Language"],
  electives: ["Computer Science", "Fine Arts", "Music", "Physical Education", "Environmental Studies"],
  specializations: ["Physics", "Chemistry", "Biology", "Commerce", "Humanities"],
};

const achievements = [
  { year: "2024", title: "National Science Olympiad", description: "3 Gold, 5 Silver medals" },
  { year: "2024", title: "Inter-School Debate Championship", description: "First Place" },
  { year: "2023", title: "State Math Competition", description: "Top 10 School Award" },
  { year: "2023", title: "Sports Excellence Award", description: "Best Athletic Program" },
];

const Academics = () => {
  return (
    <Layout>
      <Helmet>
        <title>Academics | MIENEBI International School</title>
        <meta name="description" content="Explore academics at MIENEBI International School: British & Nigerian curriculum, departments, and academic excellence." />
        <meta property="og:title" content="Academics | MIENEBI International School" />
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
              <BookOpen className="h-4 w-4" />
              <span className="text-sm font-medium">Academics</span>
            </div>
            <h1 className="font-serif text-4xl lg:text-5xl xl:text-6xl font-bold mb-6">
              Academic Excellence at MIENEBI International School Abuja
            </h1>
            <p className="text-lg text-primary-foreground/80 leading-relaxed">
              At MIENEBI International School, our comprehensive curriculum is designed to challenge and inspire students,
              fostering a love for learning that lasts a lifetime.
            </p>
          </div>
        </div>
      </section>

      {/* Grades & Curriculum */}
      <section className="py-20 lg:py-28 bg-background">
        <div className="container">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-4">
              MIENEBI International School Academic Programs
            </h2>
            <p className="text-muted-foreground text-lg">
              From early childhood to senior secondary, MIENEBI International School offers a structured
              curriculum that builds a strong foundation for future success.
            </p>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {grades.map((grade, index) => (
              <Card key={index} className="card-hover border-0 shadow-card">
                <CardHeader>
                  <div className="w-12 h-12 mb-4 rounded-xl bg-secondary/10 flex items-center justify-center">
                    <GraduationCap className="h-6 w-6 text-secondary" />
                  </div>
                  <CardTitle className="font-serif text-xl">{grade.level}</CardTitle>
                  <CardDescription>Ages: {grade.ages} at MIENEBI International School</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="flex flex-wrap gap-2">
                    {grade.classes.map((cls, i) => (
                      <span
                        key={i}
                        className="text-xs bg-accent text-accent-foreground px-3 py-1.5 rounded-full"
                      >
                        {cls}
                      </span>
                    ))}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* Subjects */}
      <section className="py-20 lg:py-28 bg-muted/50">
        <div className="container">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-4">
              Curriculum Overview
            </h2>
            <p className="text-muted-foreground text-lg">
              We offer a blend of the British and Nigerian curriculum, preparing our students for both local and international opportunities. Our curriculum is approved by WAEC and NECO.
            </p>
          </div>

          <div className="grid sm:grid-cols-2 gap-6 max-w-2xl mx-auto">
            <div className="bg-card rounded-xl p-6 flex items-center gap-4 shadow-card">
              <div className="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center shrink-0">
                <BookOpen className="h-5 w-5 text-secondary" />
              </div>
              <span className="font-medium text-foreground">British Curriculum at MIENEBI International School</span>
            </div>
            <div className="bg-card rounded-xl p-6 flex items-center gap-4 shadow-card">
              <div className="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center shrink-0">
                <BookOpen className="h-5 w-5 text-secondary" />
              </div>
              <span className="font-medium text-foreground">Nigerian Curriculum (WAEC & NECO Approved) at MIENEBI International School</span>
            </div>
          </div>
        </div>
      </section>

      {/* Academic Achievements */}
      <section className="py-20 lg:py-28 bg-background">
        <div className="container">
          <div className="grid lg:grid-cols-2 gap-16 items-center">
            <div>
              <div className="inline-flex items-center gap-2 bg-secondary/10 text-secondary-foreground px-4 py-2 rounded-full mb-6">
                <Trophy className="h-4 w-4" />
                <span className="text-sm font-medium">MIENEBI International School Achievements</span>
              </div>
              <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-6">
                MIENEBI International School Students Excel
              </h2>
              <p className="text-muted-foreground leading-relaxed mb-8">
                Year after year, MIENEBI International School students demonstrate excellence in academics,
                sports, and extracurricular activities, making us proud with their
                achievements at regional, national, and international levels.
              </p>
              <div className="grid sm:grid-cols-2 gap-4">
                {[
                  { label: "University Acceptance Rate", value: "98%" },
                  { label: "National Competition Winners", value: "120+" },
                  { label: "Scholarship Recipients", value: "45/year" },
                  { label: "Academic Awards", value: "200+" },
                ].map((stat, index) => (
                  <div key={index} className="bg-accent rounded-xl p-4">
                    <div className="font-serif text-2xl font-bold text-secondary mb-1">
                      {stat.value}
                    </div>
                    <div className="text-sm text-muted-foreground">{stat.label}</div>
                  </div>
                ))}
              </div>
            </div>
            
            <div className="space-y-4">
              {achievements.map((achievement, index) => (
                <div
                  key={index}
                  className="bg-card rounded-xl p-6 flex gap-4 shadow-card card-hover"
                >
                  <div className="bg-secondary text-secondary-foreground px-4 py-2 rounded-lg text-center shrink-0 h-fit">
                    <div className="font-serif text-lg font-bold">{achievement.year}</div>
                  </div>
                  <div>
                    <h3 className="font-serif text-lg font-bold text-primary mb-1">
                      {achievement.title} at MIENEBI International School
                    </h3>
                    <p className="text-muted-foreground text-sm">
                      {achievement.description}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Academic Calendar */}
      <section className="py-20 lg:py-28 bg-primary text-primary-foreground">
        <div className="container">
          <div className="max-w-4xl mx-auto text-center">
            <Calendar className="h-12 w-12 mx-auto mb-6 text-secondary" />
            <h2 className="font-serif text-3xl lg:text-4xl font-bold mb-4">
              MIENEBI International School Academic Calendar 2025-26
            </h2>
            <p className="text-primary-foreground/70 mb-8 max-w-2xl mx-auto">
              Plan ahead with our comprehensive academic calendar at MIENEBI International School. Download the
              complete schedule including holidays, exams, and important events.
            </p>
            <Button size="lg" className="bg-secondary hover:bg-secondary/90 text-secondary-foreground font-semibold shadow-gold">
              <Download className="mr-2 h-5 w-5" />
              Download MIENEBI Academic Calendar
            </Button>
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="py-20 lg:py-28 bg-background">
        <div className="container">
          <div className="text-center max-w-2xl mx-auto">
            <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-6">
              Start Your Academic Journey at MIENEBI International School
            </h2>
            <p className="text-muted-foreground text-lg mb-8">
              Join MIENEBI International School and experience academic excellence
              combined with holistic development.
            </p>
            <Button asChild size="lg" className="bg-secondary hover:bg-secondary/90 text-secondary-foreground font-semibold shadow-gold">
              <Link to="/admissions">
                Apply for Admission to MIENEBI International School
                <ArrowRight className="ml-2 h-5 w-5" />
              </Link>
            </Button>
          </div>
        </div>
      </section>
    </Layout>
  );
};

export default Academics;
