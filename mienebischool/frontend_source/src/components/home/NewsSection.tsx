import { ArrowRight, Calendar, Tag } from "lucide-react";
import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import newsImage1 from "@/assets/news_1.jpg";
import newsImage2 from "@/assets/news_2.jpg";
import newsImage3 from "@/assets/news_3.jpg";

const newsItems = [
  {
    id: 1,
    title: "OLD SCHOOL DAY",
    date: "2024-11-08",
    category: "Events",
    excerpt: "A day to recall past exciting experiences",
    image: newsImage1,
  },
  {
    id: 2,
    title: "SUGAR FREE WEEK PRESENTATIONS",
    date: "2024-11-05",
    category: "Health",
    excerpt: "Sugar Free Week Reviews",
    image: newsImage2,
  },
  {
    id: 3,
    title: "SUGAR FREE WEEK",
    date: "2024-11-05",
    category: "Health",
    excerpt: "Educating Learners on the benefits of sugar free diets",
    image: newsImage3,
  },
];

const NewsSection = () => {
  return (
    <section className="py-16 md:py-20 lg:py-28 bg-muted/50">
      <div className="container">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-10 md:mb-16">
          <div className="inline-flex items-center gap-2 bg-secondary/10 text-secondary-foreground px-4 py-2 rounded-full mb-4">
            <span className="text-sm font-medium">Latest Updates</span>
          </div>
          <h2 className="font-serif text-3xl lg:text-4xl xl:text-5xl font-bold text-primary mb-4">
            Latest News & Updates from MIENEBI International School
          </h2>
          <p className="text-muted-foreground text-base md:text-lg">
            Stay updated with the latest happenings, achievements, and events at
            MIENEBI International School in Abuja.
          </p>
        </div>

        {/* News Grid */}
        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3 md:gap-8">
          {newsItems.map((item, index) => (
            <article
              key={item.id}
              className="bg-card rounded-2xl overflow-hidden shadow-card card-hover group"
              style={{ animationDelay: `${index * 100}ms` }}
            >
              {/* Image */}
              <div className="relative h-48 md:h-56 overflow-hidden">
                <img
                  src={item.image}
                  alt={`${item.title} - MIENEBI International School News`}
                  className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                />
                <div className="absolute top-3 left-3 md:top-4 md:left-4">
                  <Badge variant="secondary">{item.category}</Badge>
                </div>
              </div>

              {/* Content */}
              <div className="p-5 md:p-6">
                <div className="flex items-center gap-3 md:gap-4 text-xs md:text-sm text-muted-foreground mb-2 md:mb-3">
                  <span className="flex items-center gap-1">
                    <Calendar className="h-4 w-4" />
                    {item.date}
                  </span>
                </div>
                <h3 className="font-serif text-lg md:text-xl font-bold text-primary mb-2 md:mb-3 line-clamp-2 group-hover:text-secondary transition-colors">
                  {item.title}
                </h3>
                <p className="text-muted-foreground text-sm leading-relaxed line-clamp-3 mb-4">
                  {item.excerpt}
                </p>
                <Link
                  to={`/news/${item.id}`}
                  className="inline-flex items-center gap-2 text-primary font-medium hover:text-secondary transition-colors group/link"
                >
                  Read More
                  <ArrowRight className="h-4 w-4 group-hover/link:translate-x-1 transition-transform" />
                </Link>
              </div>
            </article>
          ))}
        </div>

        {/* View All Button */}
        <div className="text-center mt-10 md:mt-12">
          <Button
            asChild
            variant="outline"
            size="lg"
            className="border-2 border-primary text-primary hover:bg-primary hover:text-primary-foreground font-semibold"
          >
            <Link to="/news">
              View All News
              <ArrowRight className="ml-2 h-5 w-5" />
            </Link>
          </Button>
        </div>
      </div>
    </section>
  );
};

export default NewsSection;
