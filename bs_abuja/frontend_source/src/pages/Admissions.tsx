import Layout from "@/components/layout/Layout";
import { Link } from "react-router-dom";
import { ArrowRight, FileText, CheckCircle, Download, HelpCircle, Users, Search } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Calendar as CalendarIcon, Upload } from "lucide-react";
import { Calendar } from "@/components/ui/calendar";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { cn } from "@/lib/utils";
import { format } from "date-fns";
import React from "react";
import { Helmet } from "react-helmet-async";

const admissionSteps = [
  {
    step: 1,
    title: "Submit Inquiry",
    description: "Fill out the online inquiry form or visit our admissions office to express interest.",
  },
  {
    step: 2,
    title: "Campus Visit",
    description: "Schedule a tour of our campus to see our facilities and meet our faculty.",
  },
  {
    step: 3,
    title: "Application Form",
    description: "Complete the application form and submit required documents.",
  },
  {
    step: 4,
    title: "Assessment",
    description: "Your child will undergo an age-appropriate assessment or interaction session.",
  },
  {
    step: 5,
    title: "Interview",
    description: "A brief interview with parents to understand expectations and share our vision.",
  },
  {
    step: 6,
    title: "Admission Offer",
    description: "Successful candidates receive an admission offer letter within a week.",
  },
];

const feeStructure = [
  { grade: "Pre-Primary (Nursery - UKG)", tuition: "$3,500", development: "$500", total: "$4,000" },
  { grade: "Primary (Grade 1-5)", tuition: "$4,500", development: "$600", total: "$5,100" },
  { grade: "Middle School (Grade 6-8)", tuition: "$5,500", development: "$700", total: "$6,200" },
  { grade: "High School (Grade 9-10)", tuition: "$6,500", development: "$800", total: "$7,300" },
  { grade: "Senior Secondary (Grade 11-12)", tuition: "$7,500", development: "$1,000", total: "$8,500" },
];

const requirements = [
  "Birth Certificate (original + copy)",
  "Previous School Report Cards (last 2 years)",
  "Transfer Certificate (if applicable)",
  "Passport-size Photographs (6 copies)",
  "Proof of Address",
  "Medical Fitness Certificate",
  "Aadhaar Card / ID Proof",
  "Parent's ID Proof",
];

const faqs = [
  {
    question: "What is the age requirement for admission to MIENEBI International School?",
    answer: "For Nursery at MIENEBI International School, the child should be 3 years old by March 31st of the admission year. Age requirements increase correspondingly for each grade level.",
  },
  {
    question: "Is there an entrance exam for MIENEBI International School admission?",
    answer: "For Pre-Primary and Primary grades, MIENEBI International School conducts an informal interaction session. For Middle and High School, there's a written assessment in English, Mathematics, and an interview.",
  },
  {
    question: "What is the student-teacher ratio at MIENEBI International School?",
    answer: "MIENEBI International School maintains a healthy student-teacher ratio of 20:1 for Primary classes and 25:1 for Middle and High School to ensure personalized attention.",
  },
  {
    question: "Does MIENEBI International School offer scholarships?",
    answer: "Yes, MIENEBI International School offers merit-based scholarships, sports scholarships, and need-based financial aid. Contact our admissions office for more details.",
  },
  {
    question: "Is transportation available at MIENEBI International School?",
    answer: "Yes, MIENEBI International School provides safe and reliable bus transportation covering major routes in the city. Transport fees are additional and vary by distance.",
  },
];

const Admissions = () => {
  const [date, setDate] = React.useState<Date>();

  const faqSchema = {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": faqs.map(faq => ({
      "@type": "Question",
      "name": faq.question,
      "acceptedAnswer": {
        "@type": "Answer",
        "text": faq.answer
      }
    }))
  };

  return (
    <Layout>
      <Helmet>
        <title>Admissions | MIENEBI International School</title>
        <meta name="description" content="Apply to MIENEBI International School in Abuja. Learn about requirements, fees, and the admissions process for British & Nigerian curriculum." />
        <meta property="og:title" content="Admissions | MIENEBI International School" />
        <script type="application/ld+json">
          {JSON.stringify(faqSchema)}
        </script>
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
              <FileText className="h-4 w-4" />
              <span className="text-sm font-medium">Admissions 2023-24</span>
            </div>
            <h1 className="font-serif text-4xl lg:text-5xl xl:text-6xl font-bold mb-6">
              MIENEBI International School Admission Form
            </h1>
            <p className="text-lg text-primary-foreground/80 leading-relaxed mb-8">
              Please fill out the form below to begin the MIENEBI International School admission process.
            </p>
          </div>
        </div>
      </section>

      {/* Admission Form */}
      <section className="py-20 lg:py-28 bg-background">
        <div className="container">
          <div className="max-w-4xl mx-auto">
            <Card className="shadow-lg">
              <CardHeader>
                <CardTitle className="font-serif text-2xl">MIENEBI International School Online Admission Form</CardTitle>
                <CardDescription>
                  Ensure all details are filled accurately for MIENEBI International School enrollment.
                </CardDescription>
              </CardHeader>
              <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="space-y-2">
                  <Label htmlFor="name">Full Name *</Label>
                  <Input id="name" placeholder="Enter your full name" />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="email">Email Address *</Label>
                  <Input id="email" type="email" placeholder="Enter your email" />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="phone">Mobile Number *</Label>
                  <Input id="phone" type="tel" placeholder="Enter your mobile number" />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="address">Address *</Label>
                  <Input id="address" placeholder="Enter your address" />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="religion">Religion *</Label>
                  <Input id="religion" placeholder="Enter your religion" />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="state">State of Origin *</Label>
                  <Input id="state" placeholder="Enter your state of origin" />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="country">Country *</Label>
                  <Input id="country" placeholder="Enter your country" />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="present-class">Present Class *</Label>
                  <Select>
                    <SelectTrigger id="present-class">
                      <SelectValue placeholder="Select present class" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="creche">Creche</SelectItem>
                      <SelectItem value="nursery">Nursery</SelectItem>
                      <SelectItem value="reception">Reception</SelectItem>
                      <SelectItem value="grade-one">Grade One</SelectItem>
                      <SelectItem value="grade-two">Grade Two</SelectItem>
                      <SelectItem value="grade-three">Grade Three</SelectItem>
                      <SelectItem value="grade-four">Grade Four</SelectItem>
                      <SelectItem value="grade-five">Grade Five</SelectItem>
                      <SelectItem value="ss1">SS 1</SelectItem>
                      <SelectItem value="ss2">SS 2</SelectItem>
                      <SelectItem value="ss3">SS 3</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="admission-class">Admission Class *</Label>
                  <Select>
                    <SelectTrigger id="admission-class">
                      <SelectValue placeholder="Select admission class" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="creche">Creche</SelectItem>
                      <SelectItem value="nursery">Nursery</SelectItem>
                      <SelectItem value="reception">Reception</SelectItem>
                      <SelectItem value="grade-one">Grade One</SelectItem>
                      <SelectItem value="grade-two">Grade Two</SelectItem>
                      <SelectItem value="grade-three">Grade Three</SelectItem>
                      <SelectItem value="grade-four">Grade Four</SelectItem>
                      <SelectItem value="grade-five">Grade Five</SelectItem>
                      <SelectItem value="ss1">SS 1</SelectItem>
                      <SelectItem value="ss2">SS 2</SelectItem>
                      <SelectItem value="ss3">SS 3</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="term">Term *</Label>
                  <Select>
                    <SelectTrigger id="term">
                      <SelectValue placeholder="Select term" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="first">First Term</SelectItem>
                      <SelectItem value="second">Second Term</SelectItem>
                      <SelectItem value="third">Third Term</SelectItem>
                      <SelectItem value="mid-term">Mid-Term</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="session">Session *</Label>
                  <Select>
                    <SelectTrigger id="session">
                      <SelectValue placeholder="Select session" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="2023-2024">2023/2024</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="section">Section *</Label>
                  <Select>
                    <SelectTrigger id="section">
                      <SelectValue placeholder="Select section" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="nursery">Nursery</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="dob">Date of Birth *</Label>
                  <Popover>
                    <PopoverTrigger asChild>
                      <Button
                        variant={"outline"}
                        className={cn(
                          "w-full justify-start text-left font-normal",
                          !date && "text-muted-foreground"
                        )}
                      >
                        <CalendarIcon className="mr-2 h-4 w-4" />
                        {date ? format(date, "PPP") : <span>Pick a date</span>}
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-auto p-0">
                      <Calendar
                        mode="single"
                        selected={date}
                        onSelect={setDate}
                        initialFocus
                      />
                    </PopoverContent>
                  </Popover>
                </div>
                <div className="space-y-2">
                  <Label>Gender *</Label>
                  <RadioGroup defaultValue="male" className="flex items-center space-x-4">
                    <div className="flex items-center space-x-2">
                      <RadioGroupItem value="male" id="male" />
                      <Label htmlFor="male">Male</Label>
                    </div>
                    <div className="flex items-center space-x-2">
                      <RadioGroupItem value="female" id="female" />
                      <Label htmlFor="female">Female</Label>
                    </div>
                  </RadioGroup>
                </div>
                <div className="space-y-2">
                  <Label>Payment Method *</Label>
                  <RadioGroup defaultValue="online" className="flex items-center space-x-4">
                    <div className="flex items-center space-x-2">
                      <RadioGroupItem value="online" id="online" />
                      <Label htmlFor="online">Online</Label>
                    </div>
                    <div className="flex items-center space-x-2">
                      <RadioGroupItem value="bank" id="bank" />
                      <Label htmlFor="bank">Bank Deposit</Label>
                    </div>
                  </RadioGroup>
                </div>
                <div className="space-y-2 md:col-span-2">
                  <Label htmlFor="photo">Student Photo *</Label>
                  <div className="flex items-center justify-center w-full">
                    <Label
                      htmlFor="photo"
                      className="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer hover:bg-muted"
                    >
                      <div className="flex flex-col items-center justify-center pt-5 pb-6">
                        <Upload className="w-8 h-8 mb-2 text-muted-foreground" />
                        <p className="mb-2 text-sm text-muted-foreground">
                          <span className="font-semibold">Click to upload</span> or drag and drop
                        </p>
                        <p className="text-xs text-muted-foreground">PNG, JPG (MAX. 800x400px)</p>
                      </div>
                      <Input id="photo" type="file" className="hidden" />
                    </Label>
                  </div>
                </div>
                <div className="md:col-span-2">
                  <Button type="submit" size="lg" className="w-full">
                    Submit Application
                  </Button>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      {/* FAQs */}
      <section className="py-20 lg:py-28 bg-muted/50">
        <div className="container">
          <div className="max-w-3xl mx-auto">
            <div className="text-center mb-16">
              <div className="inline-flex items-center gap-2 bg-secondary/10 text-secondary-foreground px-4 py-2 rounded-full mb-4">
                <HelpCircle className="h-4 w-4" />
                <span className="text-sm font-medium">FAQs</span>
              </div>
              <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-4">
                MIENEBI International School Admission FAQs
              </h2>
              <p className="text-muted-foreground text-lg">
                Find answers to common questions about admissions at MIENEBI International School.
              </p>
            </div>

            <div className="relative mb-8">
              <Input
                placeholder="Search FAQs..."
                className="w-full pl-10"
              />
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" />
            </div>

            <Accordion type="single" collapsible className="space-y-4">
              {faqs.map((faq, index) => (
                <AccordionItem
                  key={index}
                  value={`item-${index}`}
                  className="bg-card rounded-xl px-6 border-0 shadow-card"
                >
                  <AccordionTrigger className="hover:no-underline py-6">
                    <span className="font-serif text-lg font-bold text-left">
                      {faq.question}
                    </span>
                  </AccordionTrigger>
                  <AccordionContent className="pb-6 text-muted-foreground leading-relaxed">
                    {faq.answer}
                  </AccordionContent>
                </AccordionItem>
              ))}
            </Accordion>
          </div>
        </div>
      </section>
    </Layout>
  );
};

export default Admissions;
