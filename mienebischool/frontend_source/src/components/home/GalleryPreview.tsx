import { ArrowRight, Camera } from "lucide-react";
import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";
import gallery1 from "@/assets/gallery_academic_1.jpg";
import gallery2 from "@/assets/gallery_sports_1.jpg";
import gallery3 from "@/assets/gallery_facilities_1.jpg";
import gallery4 from "@/assets/gallery_events_1.jpg";
import gallery5 from "@/assets/gallery_facilities_2.jpg";
import gallery6 from "@/assets/gallery_academic_2.jpg";

const galleryImages = [
  { id: 1, src: gallery1, alt: "Students in classroom", category: "Academic" },
  { id: 2, src: gallery2, alt: "Sports activities", category: "Sports" },
  { id: 3, src: gallery3, alt: "Science lab", category: "Facilities" },
  { id: 4, src: gallery4, alt: "Cultural event", category: "Events" },
  { id: 5, src: gallery5, alt: "Library", category: "Facilities" },
  { id: 6, src: gallery6, alt: "Art class", category: "Academic" },
];

const GalleryPreview = () => {
  return (
    <section className="py-20 lg:py-28 bg-primary text-primary-foreground relative overflow-hidden">
      {/* Decorative Elements */}
      <div className="absolute top-20 left-10 w-64 h-64 bg-secondary/10 rounded-full blur-3xl" />
      <div className="absolute bottom-20 right-10 w-80 h-80 bg-secondary/5 rounded-full blur-3xl" />

      <div className="container relative">
        {/* Section Header */}
        <div className="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6 mb-12">
          <div>
            <div className="inline-flex items-center gap-2 bg-secondary/20 text-primary-foreground px-4 py-2 rounded-full mb-4">
              <Camera className="h-4 w-4" />
              <span className="text-sm font-medium">Photo Gallery</span>
            </div>
            <h2 className="font-serif text-3xl lg:text-4xl xl:text-5xl font-bold mb-4">
              MIENEBI International School Photo Gallery: Glimpses of Our Abuja Campus Life
            </h2>
            <p className="text-primary-foreground/70 max-w-xl">
              Explore moments of learning, friendship, and growth captured across
              our vibrant campus at MIENEBI International School.
            </p>
          </div>
          <Button
            asChild
            className="bg-secondary hover:bg-secondary/90 text-primary font-bold shadow-gold shrink-0 transition-all active:scale-95"
          >
            <Link to="/gallery">
              View Full Gallery
              <ArrowRight className="ml-2 h-5 w-5" />
            </Link>
          </Button>
        </div>

        {/* Gallery Grid */}
        <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
          {galleryImages.map((image, index) => (
            <Link
              key={image.id}
              to="/gallery"
              className={`group relative overflow-hidden rounded-xl ${index === 0 ? "md:col-span-2 md:row-span-2" : ""
                }`}
            >
              <div className={`${index === 0 ? "aspect-square" : "aspect-[4/3]"}`}>
                <img
                  src={image.src}
                  alt={`${image.alt} at MIENEBI International School Abuja`}
                  className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                />
              </div>
              {/* Overlay */}
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
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
};

export default GalleryPreview;
