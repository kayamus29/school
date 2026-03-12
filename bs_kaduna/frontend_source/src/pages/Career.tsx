import Layout from "@/components/layout/Layout";
import { Helmet } from "react-helmet-async";

const Career = () => {
  return (
    <Layout>
      <Helmet>
        <title>Careers | MIENEBI International School</title>
        <meta name="description" content="Join MIENEBI International School in Abuja. Teaching, administration, and staff opportunities with growth and impact." />
        <meta property="og:title" content="Careers | MIENEBI International School" />
      </Helmet>
      <section className="py-20 lg:py-28 bg-background">
        <div className="container">
          <div className="max-w-2xl mx-auto text-center">
            <h1 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-6">
              Careers at MIENEBI International School Abuja | Join Our Team
            </h1>
            <p className="text-muted-foreground text-lg mb-8">
              Join our team of dedicated educators and staff at MIENEBI International School in Gwarimpa Estate, Abuja.
            </p>
          </div>
        </div>
      </section>
    </Layout>
  );
};

export default Career;
