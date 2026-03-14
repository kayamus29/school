import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Award, Shield, Beaker, Users, CheckCircle, Star, TrendingUp } from "lucide-react";

const reasons = [
  {
    icon: Award,
    title: "International Recognition",
    description: "Awarded for excellence in events like the Spelling Bee.",
  },
  {
    icon: CheckCircle,
    title: "100% Examination Success",
    description: "A near-perfect success rate of 99.99% in examinations.",
  },
  {
    icon: Shield,
    title: "Security",
    description: "The safety and security of our students is our utmost priority.",
  },
  {
    icon: Beaker,
    title: "Well-Equipped Laboratory",
    description: "Hands-on practical experience in our modern science labs.",
  },
  {
    icon: Users,
    title: "Conducive Learning Environment",
    description: "A positive and supportive atmosphere for effective learning.",
  },
  {
    icon: TrendingUp,
    title: "Mass Success in External Examination",
    description: "Proven track record of success in external examinations.",
  },
  {
    icon: Star,
    title: "Student Independence",
    description: "Our students are trained to be self-reliant and independent thinkers.",
  },
];

const WhyChooseUs = () => {
  return (
    <section className="py-16 md:py-20 lg:py-28 bg-background">
      <div className="container">
        <div className="text-center max-w-2xl mx-auto mb-10 md:mb-16">
          <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-4">
            Why Parents Choose MIENEBI International School in Abuja
          </h2>
          <p className="text-muted-foreground text-lg">
            Discover the reasons why families trust MIENEBI International School with their children's education and future.
          </p>
        </div>
        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 sm:gap-6">
          {reasons.map((reason, index) => (
            <Card key={index} className="text-center">
              <CardHeader>
                <div className="w-14 h-14 md:w-16 md:h-16 mx-auto mb-4 rounded-xl bg-secondary/10 flex items-center justify-center">
                  <reason.icon className="h-7 w-7 md:h-8 md:w-8 text-secondary" />
                </div>
                <CardTitle className="font-serif text-lg sm:text-xl">{reason.title}</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">{reason.description.replace("Awarded", "MIENEBI International School awarded").replace("A near-perfect", "MIENEBI International School achieves a near-perfect")}</p>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </section>
  );
};

export default WhyChooseUs;
