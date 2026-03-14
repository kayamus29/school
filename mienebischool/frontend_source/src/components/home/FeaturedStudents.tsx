import { User } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import headboy from "@/assets/student_headboy.jpg";
import headgirl from "@/assets/student_headgirl.jpg";
import asst_headboy from "@/assets/student_asst_headboy.jpg";
import asst_headgirl from "@/assets/student_asst_headgirl.jpg";

const students = [
  {
    name: "ATULUKU BENJAMIN",
    role: "Head Boy",
    image: headboy,
  },
  {
    name: "MALLAM KANDE FAVOUR",
    role: "Head Girl",
    image: headgirl,
  },
  {
    name: "ATUBA ARINZE",
    role: "Assistant Head Boy",
    image: asst_headboy,
  },
  {
    name: "ADALIKWU MELONEE",
    role: "Assistant Head Girl",
    image: asst_headgirl,
  },
];

const FeaturedStudents = () => {
  return (
    <section className="py-20 lg:py-28 bg-background">
      <div className="container">
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-4">
            Featured Students of the Week at MIENEBI International School
          </h2>
          <p className="text-muted-foreground text-lg">
            Celebrating our outstanding student leaders for their exemplary
            contributions to our school community.
          </p>
        </div>
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {students.map((student, index) => (
            <Card key={index} className="text-center">
              <CardHeader>
                <div className="relative w-32 h-32 mx-auto mb-4">
                  <img
                    src={student.image}
                    alt={`${student.name}, ${student.role} at MIENEBI International School`}
                    className="w-full h-full rounded-full object-cover border-4 border-secondary"
                  />
                  <div className="absolute bottom-0 right-0 bg-primary text-primary-foreground p-2 rounded-full">
                    <User className="h-5 w-5" />
                  </div>
                </div>
                <CardTitle className="font-serif text-xl">{student.name}</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">{student.role}</p>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </section>
  );
};

export default FeaturedStudents;
