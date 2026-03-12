import Layout from "@/components/layout/Layout";
import { useState } from "react";
import { Send } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useToast } from "@/hooks/use-toast";
import { Helmet } from "react-helmet-async";

const Services = () => {
  const { toast } = useToast();
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    phone: "",
    service: "",
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSelectChange = (value: string) => {
    setFormData({ ...formData, service: value });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    
    // Simulate form submission
    await new Promise((resolve) => setTimeout(resolve, 1000));
    
    toast({
      title: "Request Sent!",
      description: "We'll get back to you shortly.",
    });
    
    setFormData({ name: "", email: "", phone: "", service: "" });
    setIsSubmitting(false);
  };

  return (
    <Layout>
      <Helmet>
        <title>Services | MIENEBI International School</title>
        <meta name="description" content="Explore services at MIENEBI International School: transport, extracurriculars, counseling, and more." />
        <meta property="og:title" content="Services | MIENEBI International School" />
      </Helmet>
      <section className="py-20 lg:py-28 bg-background">
        <div className="container">
          <div className="max-w-2xl mx-auto text-center">
            <h1 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-6">
              MIENEBI International School Service Request Form Abuja
            </h1>
            <p className="text-muted-foreground text-lg mb-8">
              Have a question or need assistance at MIENEBI International School? Fill out the form below and we'll be happy to help.
            </p>
          </div>
          <div className="max-w-xl mx-auto">
            <form onSubmit={handleSubmit} className="space-y-6 bg-card p-8 rounded-xl shadow-card">
              <div className="space-y-2">
                <Label htmlFor="name">Full Name *</Label>
                <Input
                  id="name"
                  name="name"
                  value={formData.name}
                  onChange={handleChange}
                  placeholder="John Doe"
                  required
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="email">Email Address *</Label>
                <Input
                  id="email"
                  name="email"
                  type="email"
                  value={formData.email}
                  onChange={handleChange}
                  placeholder="john@example.com"
                  required
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="phone">Phone Number</Label>
                <Input
                  id="phone"
                  name="phone"
                  type="tel"
                  value={formData.phone}
                  onChange={handleChange}
                  placeholder="+1 (234) 567-890"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="service">Service *</Label>
                <Select
                  name="service"
                  value={formData.service}
                  onValueChange={handleSelectChange}
                  required
                >
                  <SelectTrigger id="service">
                    <SelectValue placeholder="Select a service" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Admission Process">Admission Process</SelectItem>
                    <SelectItem value="School Fees">School Fees</SelectItem>
                    <SelectItem value="General Consultancy">General Consultancy</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <Button
                type="submit"
                size="lg"
                disabled={isSubmitting}
                className="w-full bg-secondary hover:bg-secondary/90 text-secondary-foreground font-semibold"
              >
                {isSubmitting ? (
                  "Sending..."
                ) : (
                  <>
                    Send Request
                    <Send className="ml-2 h-5 w-5" />
                  </>
                )}
              </Button>
            </form>
          </div>
        </div>
      </section>
    </Layout>
  );
};

export default Services;
