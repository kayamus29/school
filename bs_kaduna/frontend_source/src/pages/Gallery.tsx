import Layout from "@/components/layout/Layout";
import { useState } from "react";
import { Link } from "react-router-dom";
import { ArrowRight, Camera, Play, Filter, X } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogClose } from "@/components/ui/dialog";
import gallery1 from "@/assets/gallery_academic_1.jpg";
import gallery2 from "@/assets/gallery_sports_1.jpg";
import gallery3 from "@/assets/gallery_facilities_1.jpg";
import gallery4 from "@/assets/gallery_events_1.jpg";
import gallery5 from "@/assets/gallery_facilities_2.jpg";
import gallery6 from "@/assets/gallery_academic_2.jpg";
import { Helmet } from "react-helmet-async";

const galleryImages = [
  { id: 1, src: gallery1, alt: "Classroom learning", category: "Academic", featured: true },
  { id: 2, src: gallery2, alt: "Sports activities", category: "Sports", featured: false },
  { id: 3, src: gallery3, alt: "Science laboratory", category: "Facilities", featured: true },
  { id: 4, src: gallery4, alt: "Cultural festival", category: "Events", featured: false },
  { id: 5, src: gallery5, alt: "Library", category: "Facilities", featured: false },
  { id: 6, src: gallery6, alt: "Art class", category: "Academic", featured: false },
  { id: 7, src: gallery1, alt: "Group study", category: "Academic", featured: false },
  { id: 8, src: gallery2, alt: "Athletics meet", category: "Sports", featured: true },
  { id: 9, src: gallery3, alt: "Chemistry experiment", category: "Academic", featured: false },
  { id: 10, src: gallery4, alt: "Annual day", category: "Events", featured: false },
  { id: 11, src: gallery5, alt: "Reading session", category: "Facilities", featured: false },
  { id: 12, src: gallery6, alt: "Painting workshop", category: "Events", featured: false },
];

const categories = ["All", "Academic", "Sports", "Events", "Facilities"];

const Gallery = () => {
  const [selectedCategory, setSelectedCategory] = useState("All");
  const [selectedImage, setSelectedImage] = useState<typeof galleryImages[0] | null>(null);

  const filteredImages =
    selectedCategory === "All"
      ? galleryImages
      : galleryImages.filter((img) => img.category === selectedCategory);

  return (
    <Layout>
      <Helmet>
        <title>Gallery | MIENEBI International School</title>
        <meta name="description" content="Explore the MIENEBI International School gallery: campus life, academics, sports, and events in Abuja." />
        <meta property="og:title" content="Gallery | MIENEBI International School" />
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
              <Camera className="h-4 w-4" />
              <span className="text-sm font-medium">Photo Gallery</span>
            </div>
            <h1 className="font-serif text-4xl lg:text-5xl xl:text-6xl font-bold mb-6">
              MIENEBI International School Photo Gallery: Moments That Matter in Abuja
            </h1>
            <p className="text-lg text-primary-foreground/80 leading-relaxed">
              Explore the vibrant life at MIENEBI International School in Gwarimpa Estate, Abuja, through our
              collection of photos capturing learning, friendship, and growth.
            </p>
          </div>
        </div>
      </section>

      {/* Gallery */}
      <section className="py-20 lg:py-28 bg-background">
        <div className="container">
          {/* Filter */}
          <div className="flex flex-wrap items-center justify-center gap-3 mb-12">
            <Filter className="h-5 w-5 text-muted-foreground" />
            {categories.map((category) => (
              <Button
                key={category}
                variant={selectedCategory === category ? "default" : "outline"}
                onClick={() => setSelectedCategory(category)}
                className={
                  selectedCategory === category
                    ? "bg-secondary hover:bg-secondary/90 text-secondary-foreground"
                    : "border-2"
                }
              >
                {category}
              </Button>
            ))}
          </div>

          {/* Image Grid */}
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {filteredImages.map((image, index) => (
              <button
                key={image.id}
                onClick={() => setSelectedImage(image)}
                className={`group relative overflow-hidden rounded-xl ${image.featured && index < 3 ? "col-span-2 row-span-2" : ""
                  }`}
              >
                <div className={`${image.featured && index < 3 ? "aspect-square" : "aspect-[4/3]"}`}>
                  <img
                    src={image.src}
                    alt={`${image.alt} at MIENEBI International School Abuja`}
                    className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                  />
                </div>
                <div className="absolute inset-0 bg-gradient-to-t from-primary/80 via-primary/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                  <div>
                    <span className="text-xs bg-secondary text-secondary-foreground px-2 py-1 rounded-full">
                      {image.category}
                    </span>
                    <p className="text-primary-foreground font-medium mt-2">
                      {image.alt}
                    </p>
                  </div>
                </div>
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* Video Section */}
      <section className="py-20 lg:py-28 bg-muted/50">
        <div className="container">
          <div className="text-center max-w-2xl mx-auto mb-12">
            <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-4">
              MIENEBI International School Video Gallery
            </h2>
            <p className="text-muted-foreground text-lg">
              Watch videos from MIENEBI International School events, activities, and campus life.
            </p>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {[1, 2, 3].map((_, index) => (
              <div
                key={index}
                className="bg-card rounded-2xl overflow-hidden shadow-card card-hover"
              >
                <div className="aspect-video bg-primary/10 relative flex items-center justify-center">
                  <div className="w-16 h-16 rounded-full bg-secondary flex items-center justify-center cursor-pointer hover:scale-110 transition-transform">
                    <Play className="h-7 w-7 text-secondary-foreground ml-1" />
                  </div>
                </div>
                <div className="p-4">
                  <h3 className="font-serif font-bold text-primary mb-1">
                    {["MIENEBI Annual Day 2024", "MIENEBI Sports Championship", "MIENEBI Science Exhibition"][index]}
                  </h3>
                  <p className="text-sm text-muted-foreground">
                    {["Dec 15, 2024", "Nov 20, 2024", "Oct 10, 2024"][index]}
                  </p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Lightbox Dialog */}
      <Dialog open={!!selectedImage} onOpenChange={() => setSelectedImage(null)}>
        <DialogContent className="max-w-4xl p-0 bg-transparent border-0">
          <div className="relative">
            {selectedImage && (
              <img
                src={selectedImage.src}
                alt={`${selectedImage.alt} at MIENEBI International School Abuja`}
                className="w-full h-auto rounded-xl"
              />
            )}
            <DialogClose className="absolute top-4 right-4 w-10 h-10 rounded-full bg-primary/80 text-primary-foreground flex items-center justify-center hover:bg-primary transition-colors">
              <X className="h-5 w-5" />
            </DialogClose>
            {selectedImage && (
              <div className="absolute bottom-4 left-4 bg-primary/80 text-primary-foreground px-4 py-2 rounded-lg">
                <span className="text-xs bg-secondary text-secondary-foreground px-2 py-1 rounded-full mr-2">
                  {selectedImage.category}
                </span>
                <span className="font-medium">{`${selectedImage.alt} at MIENEBI International School Abuja`}</span>
              </div>
            )}
          </div>
        </DialogContent>
      </Dialog>

      {/* CTA */}
      <section className="py-20 lg:py-28 bg-primary text-primary-foreground">
        <div className="container">
          <div className="text-center max-w-2xl mx-auto">
            <h2 className="font-serif text-3xl lg:text-4xl font-bold mb-6">
              Experience MIENEBI International School Campus Life
            </h2>
            <p className="text-primary-foreground/70 text-lg mb-8">
              Want to see more? Schedule a campus visit and experience
              MIENEBI International School firsthand.
            </p>
            <Button asChild size="lg" className="bg-secondary hover:bg-secondary/90 text-secondary-foreground font-semibold shadow-gold">
              <Link to="/contact">
                Schedule a Visit
                <ArrowRight className="ml-2 h-5 w-5" />
              </Link>
            </Button>
          </div>
        </div>
      </section>
    </Layout>
  );
};

export default Gallery;
