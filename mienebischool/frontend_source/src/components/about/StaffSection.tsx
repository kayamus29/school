import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { User } from "lucide-react";
import coordinator_grade from "@/assets/staff_grade_coordinator.jpg";
import coordinator_earlyyears from "@/assets/staff_earlyyears_coordinator.jpg";
import vice_principal from "@/assets/staff_vice_principal.jpg";

const staff = [
  {
    name: "Mrs. Wogu",
    role: "Grade Section Coordinator",
    image: coordinator_grade,
  },
  {
    name: "Mrs Esosa",
    role: "Early Years Section Coordinator",
    image: coordinator_earlyyears,
  },
  {
    name: "Patrick",
    role: "Vice Principal",
    image: vice_principal,
  },
];

const StaffSection = () => {
  return (
    <section className="py-20 lg:py-28 bg-background">
      <div className="container">
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-4">
            Our Dedicated Team
          </h2>
          <p className="text-muted-foreground text-lg">
            Meet the leaders who guide our students and shape our school's success.
          </p>
        </div>
        <div className="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {staff.map((member, index) => (
            <Card key={index} className="text-center">
              <CardHeader>
                <div className="relative w-32 h-32 mx-auto mb-4">
                  <img
                    src={member.image}
                    alt={member.name}
                    className="w-full h-full rounded-full object-cover border-4 border-secondary"
                  />
                  <div className="absolute bottom-0 right-0 bg-primary text-primary-foreground p-2 rounded-full">
                    <User className="h-5 w-5" />
                  </div>
                </div>
                <CardTitle className="font-serif text-xl">{member.name}</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">{member.role}</p>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </section>
  );
};

export default StaffSection;
