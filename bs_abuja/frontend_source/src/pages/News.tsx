import Layout from "@/components/layout/Layout";
import { Link } from "react-router-dom";
import { Calendar, Tag, ArrowRight } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import news1 from "@/assets/news_1.jpg";
import news1_alt from "@/assets/news_1.jpg";
import news2 from "@/assets/news_2.jpg";
import news3 from "@/assets/news_3.jpg";
import { Helmet } from "react-helmet-async";

const blogPosts = [
  {
    id: 1,
    title: "OLD SCHOOL DAY",
    date: "2024-11-08",
    category: "Events",
    excerpt: "A day to recall past exciting experiences",
    image: news1,
  },
  {
    id: 2,
    title: "SUGAR FREE WEEK PRESENTATIONS",
    date: "2024-11-05",
    category: "Health",
    excerpt: "Sugar Free Week Reviews",
    image: news2,
  },
  {
    id: 3,
    title: "SUGAR FREE WEEK",
    date: "2024-11-05",
    category: "Health",
    excerpt: "Educating Learners on the benefits of sugar free diets",
    image: news3,
  },
  {
    id: 4,
    title: "OUR TEACHERS",
    date: "2024-10-24",
    category: "School",
    excerpt: "Our Teachers always makes us proud",
    image: news1,
  },
  {
    id: 5,
    title: "COWBELLPEDIA",
    date: "2024-10-09",
    category: "Academics",
    excerpt: "Science quiz competition",
    image: news2,
  },
  {
    id: 6,
    title: "SPORTS DAY",
    date: "2024-10-09",
    category: "Sports",
    excerpt: "We love sports",
    image: news3,
  },
  {
    id: 7,
    title: "CREATIVITY",
    date: "2024-10-09",
    category: "Academics",
    excerpt: "Be smart, be creative",
    image: news1,
  },
  {
    id: 8,
    title: "AGRICULTURAL PRACTICES",
    date: "2024-10-08",
    category: "Academics",
    excerpt: "an outdoor adventure",
    image: news2,
  },
];

const News = () => {
  return (
    <Layout>
      <Helmet>
        <title>Blog & News | MIENEBI International School</title>
        <meta name="description" content="Read the latest news, events, and updates from MIENEBI International School, Abuja." />
        <meta property="og:title" content="Blog & News | MIENEBI International School" />
      </Helmet>
      <section className="py-20 lg:py-28 bg-background">
        <div className="container">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <h1 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-4">
              MIENEBI International School News & Updates Abuja
            </h1>
            <p className="text-muted-foreground text-lg">
              Stay up-to-date with the latest news, events, and announcements from MIENEBI International School in Gwarimpa Estate, Abuja.
            </p>
          </div>
          <div className="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {blogPosts.map((post) => (
              <Card key={post.id} className="overflow-hidden">
                <Link to={`/news/${post.id}`}>
                  <img src={post.image} alt={`${post.title} - MIENEBI International School News`} className="w-full h-56 object-cover" />
                </Link>
                <CardHeader>
                  <div className="flex items-center gap-4 text-sm text-muted-foreground mb-2">
                    <div className="flex items-center gap-2">
                      <Calendar className="h-4 w-4" />
                      <span>{post.date}</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Tag className="h-4 w-4" />
                      <Badge variant="secondary">{post.category}</Badge>
                    </div>
                  </div>
                  <CardTitle className="font-serif text-xl">
                    <Link to={`/news/${post.id}`}>{post.title}</Link>
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-muted-foreground mb-4">{post.excerpt}</p>
                  <Link to={`/news/${post.id}`} className="font-semibold text-primary">
                    Read More <ArrowRight className="inline-block h-4 w-4" />
                  </Link>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>
    </Layout>
  );
};

export default News;
