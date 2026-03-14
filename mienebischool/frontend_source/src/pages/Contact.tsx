import Layout from "@/components/layout/Layout";
import { useState } from "react";
import { MapPin, Phone, Mail, Clock, Send, MessageSquare } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { useToast } from "@/hooks/use-toast";
import { Helmet } from "react-helmet-async";

const Contact = () => {
  const { toast } = useToast();
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    phone: "",
    subject: "",
    message: "",
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    
    // Simulate form submission
    await new Promise((resolve) => setTimeout(resolve, 1000));
    
    toast({
      title: "Message Sent!",
      description: "We'll get back to you within 24 hours.",
    });
    
    setFormData({ name: "", email: "", phone: "", subject: "", message: "" });
    setIsSubmitting(false);
  };

  const contactInfo = [
    {
      icon: MapPin,
      title: "Our Address",
      content: "Ps. Egure Street, Off Wole Soyinka Street, 2nd Avenue, Behind Glass House, Gwarimpa Estate, FCT Abuja",
    },
    {
      icon: Phone,
      title: "Phone Numbers",
      content: "08035983003",
      subContent: "09095201652",
    },
    {
      icon: Mail,
      title: "Email Address",
      content: "mienebisch@gmail.com",
    },
    {
      icon: Clock,
      title: "Office Hours",
      content: "Monday - Friday: 8:00 AM - 4:00 PM",
      subContent: "Saturday: 9:00 AM - 1:00 PM",
    },
  ];

  return (
    <Layout>
      <Helmet>
        <title>Contact | MIENEBI International School</title>
        <meta name="description" content="Contact MIENEBI International School, Abuja. Address, phone, email, and office hours." />
        <meta property="og:title" content="Contact | MIENEBI International School" />
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
              <MessageSquare className="h-4 w-4" />
              <span className="text-sm font-medium">Contact Us</span>
            </div>
            <h1 className="font-serif text-4xl lg:text-5xl xl:text-6xl font-bold mb-6">
              Contact MIENEBI International School in Gwarimpa Estate, Abuja
            </h1>
            <p className="text-lg text-primary-foreground/80 leading-relaxed">
              Have questions about admissions, academics, or anything else at MIENEBI International School?
              We'd love to hear from you. Reach out and we'll respond as soon
              as possible.
            </p>
          </div>
        </div>
      </section>

      {/* Contact Content */}
      <section className="py-20 lg:py-28 bg-background">
        <div className="container">
          <div className="grid lg:grid-cols-5 gap-16">
            {/* Contact Form */}
            <div className="lg:col-span-3">
              <h2 className="font-serif text-2xl lg:text-3xl font-bold text-primary mb-6">
                Send a Message to MIENEBI International School
              </h2>
              <form onSubmit={handleSubmit} className="space-y-6">
                <div className="grid sm:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <Label htmlFor="name">Full Name *</Label>
                    <Input
                      id="name"
                      name="name"
                      value={formData.name}
                      onChange={handleChange}
                      placeholder="John Doe"
                      required
                      className="bg-card"
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
                      className="bg-card"
                    />
                  </div>
                </div>
                
                <div className="grid sm:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <Label htmlFor="phone">Phone Number</Label>
                    <Input
                      id="phone"
                      name="phone"
                      type="tel"
                      value={formData.phone}
                      onChange={handleChange}
                      placeholder="+1 (234) 567-890"
                      className="bg-card"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="subject">Subject *</Label>
                    <Input
                      id="subject"
                      name="subject"
                      value={formData.subject}
                      onChange={handleChange}
                      placeholder="How can we help?"
                      required
                      className="bg-card"
                    />
                  </div>
                </div>
                
                <div className="space-y-2">
                  <Label htmlFor="message">Message *</Label>
                  <Textarea
                    id="message"
                    name="message"
                    value={formData.message}
                    onChange={handleChange}
                    placeholder="Tell us more about your inquiry..."
                    rows={6}
                    required
                    className="bg-card resize-none"
                  />
                </div>
                
                <Button
                  type="submit"
                  size="lg"
                  disabled={isSubmitting}
                  className="bg-secondary hover:bg-secondary/90 text-secondary-foreground font-semibold shadow-gold"
                >
                  {isSubmitting ? (
                    "Sending..."
                  ) : (
                    <>
                      Send Message
                      <Send className="ml-2 h-5 w-5" />
                    </>
                  )}
                </Button>
              </form>
            </div>

            {/* Contact Info */}
            <div className="lg:col-span-2">
              <h2 className="font-serif text-2xl lg:text-3xl font-bold text-primary mb-6">
                MIENEBI International School Contact Information
              </h2>
              <div className="space-y-6">
                {contactInfo.map((item, index) => (
                  <div
                    key={index}
                    className="bg-card rounded-xl p-6 flex gap-4 shadow-card"
                  >
                    <div className="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center shrink-0">
                      <item.icon className="h-6 w-6 text-secondary" />
                    </div>
                    <div>
                      <h3 className="font-serif text-lg font-bold text-primary mb-1">
                        {item.title}
                      </h3>
                      <p className="text-muted-foreground text-sm">
                        {item.content}
                      </p>
                      {item.subContent && (
                        <p className="text-muted-foreground text-sm">
                          {item.subContent}
                        </p>
                      )}
                    </div>
                  </div>
                ))}
              </div>

              {/* Quick Links */}
              <div className="mt-8 bg-primary text-primary-foreground rounded-xl p-6">
                <h3 className="font-serif text-lg font-bold mb-4">
                  MIENEBI International School Department Contacts
                </h3>
                <ul className="space-y-3 text-sm">
                  <li className="flex justify-between">
                    <span className="text-primary-foreground/70">Admissions</span>
                    <span>Ext. 101</span>
                  </li>
                  <li className="flex justify-between">
                    <span className="text-primary-foreground/70">Academics</span>
                    <span>Ext. 102</span>
                  </li>
                  <li className="flex justify-between">
                    <span className="text-primary-foreground/70">Finance</span>
                    <span>Ext. 103</span>
                  </li>
                  <li className="flex justify-between">
                    <span className="text-primary-foreground/70">Sports</span>
                    <span>Ext. 104</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Map Section */}
      <section className="h-96 bg-muted relative">
        <div className="absolute inset-0 flex items-center justify-center bg-muted">
          <div className="text-center">
            <MapPin className="h-12 w-12 mx-auto mb-4 text-muted-foreground" />
            <p className="text-muted-foreground">
              Interactive Google Map would be embedded here
            </p>
            <p className="text-sm text-muted-foreground/70">
              Ps. Egure Street, Off Wole Soyinka Street, 2nd Avenue, Behind Glass House, Gwarimpa Estate, FCT Abuja
            </p>
          </div>
        </div>
      </section>
    </Layout>
  );
};

export default Contact;
