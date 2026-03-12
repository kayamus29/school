import Layout from "@/components/layout/Layout";
import HeroSection from "@/components/home/HeroSection";
import StatsSection from "@/components/home/StatsSection";
import WelcomeSection from "@/components/home/WelcomeSection";
import FacilitiesSection from "@/components/home/FacilitiesSection";
import WhyChooseUs from "@/components/home/WhyChooseUs";
import ServicesSection from "@/components/home/ServicesSection";
import NewsSection from "@/components/home/NewsSection";
import EventsSection from "@/components/home/EventsSection";
import GalleryPreview from "@/components/home/GalleryPreview";
import TestimonialsSection from "@/components/home/TestimonialsSection";
import CTASection from "@/components/home/CTASection";
import { Helmet } from "react-helmet-async";

const Index = () => {
  return (
    <Layout>
      <Helmet>
        <title>MIENEBI International School | Abuja</title>
        <meta name="description" content="Top-tier British & Nigerian curriculum in Gwarimpa, Abuja. Enroll at MIENEBI International School for exceptional education and world-class facilities." />
        <meta property="og:title" content="MIENEBI International School" />
        <meta property="og:site_name" content="MIENEBI International School" />
      </Helmet>
      <HeroSection />
      <StatsSection />
      <WelcomeSection />
      <FacilitiesSection />
      <WhyChooseUs />
      <ServicesSection />
      <NewsSection />
      <EventsSection />
      <GalleryPreview />
      <TestimonialsSection />
      <CTASection />
    </Layout>
  );
};

export default Index;
