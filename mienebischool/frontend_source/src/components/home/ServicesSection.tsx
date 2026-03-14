import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { School, Baby, Book, Laptop } from "lucide-react";

const services = [
  {
    icon: Baby,
    title: "Pre-School Education",
    description: "Conducive learning environment for children from creche to reception.",
  },
  {
    icon: Book,
    title: "Primary Education",
    description: "Well-built and equipped classrooms for Basic Education.",
  },
  {
    icon: Laptop,
    title: "Secondary School Education",
    description: "Digital equipment to improve education quality.",
  },
];

const ServicesSection = () => {
  return (
    <section className="py-16 md:py-20 lg:py-28 bg-background">
      <div className="container">
        <div className="text-center max-w-2xl mx-auto mb-10 md:mb-16">
          <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-4">
            Educational Services Offered at MIENEBI International School Abuja
          </h2>
          <p className="text-muted-foreground text-base md:text-lg">
            MIENEBI International School in Abuja provides a comprehensive range of educational services to cater to the needs of our students.
          </p>
        </div>
        <div className="grid gap-5 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 sm:gap-6">
          {services.map((service, index) => (
            <Card key={index} className="text-center">
              <CardHeader>
                <div className="w-14 h-14 md:w-16 md:h-16 mx-auto mb-4 rounded-xl bg-secondary/10 flex items-center justify-center">
                  <service.icon className="h-7 w-7 md:h-8 md:w-8 text-secondary" />
                </div>
                <CardTitle className="font-serif text-lg sm:text-xl">{service.title}</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">{service.description.replace("Conducive learning environment", "MIENEBI provides a conducive learning environment")}</p>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </section>
  );
};

export default ServicesSection;
