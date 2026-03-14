import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Tv, Flame, Beaker, Drama } from "lucide-react";

const facilities = [
  {
    icon: Tv,
    title: "ICT Lab",
    description: "A well-equipped laboratory for practical ICT training.",
  },
  {
    icon: Drama,
    title: "Volley Ball Court",
    description: "A standard facility for our students to enjoy and practice volleyball.",
  },
  {
    icon: Flame,
    title: "Table Tennis Court",
    description: "A conducive environment for students to play table tennis.",
  },
  {
    icon: Beaker,
    title: "Science Laboratory",
    description: "A well-equipped laboratory for practical science experiments.",
  },
];

const FacilitiesSection = () => {
  return (
    <section className="py-14 sm:py-20 lg:py-28 bg-muted/50">
      <div className="container">
        <div className="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
          <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-4">
            Our State-of-the-Art Facilities at MIENEBI International School Abuja
          </h2>
          <p className="text-muted-foreground text-lg">
            MIENEBI International School provides a range of facilities to support our students' learning and development.
          </p>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
          {facilities.map((facility, index) => (
            <Card key={index} className="text-center">
              <CardHeader>
                <div className="w-14 h-14 sm:w-16 sm:h-16 mx-auto mb-4 rounded-xl bg-secondary/10 flex items-center justify-center">
                  <facility.icon className="h-7 w-7 sm:h-8 sm:w-8 text-secondary" />
                </div>
                <CardTitle className="font-serif text-xl">{facility.title}</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">{facility.description.replace("our students", "MIENEBI students")}</p>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </section>
  );
};

export default FacilitiesSection;
