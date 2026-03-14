import { ArrowRight, MapPin, Clock } from "lucide-react";
import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";

const events = [
  {
    id: 1,
    title: "Parent-Teacher Meeting",
    date: "25",
    month: "Jan",
    time: "10:00 AM - 2:00 PM",
    location: "Main Auditorium",
    description: "Quarterly parent-teacher conference to discuss student progress and goals.",
    featured: true,
  },
  {
    id: 2,
    title: "Annual Cultural Festival",
    date: "02",
    month: "Feb",
    time: "9:00 AM - 5:00 PM",
    location: "School Campus",
    description: "A celebration of art, music, dance, and cultural diversity.",
    featured: false,
  },
  {
    id: 3,
    title: "Career Guidance Workshop",
    date: "10",
    month: "Feb",
    time: "11:00 AM - 1:00 PM",
    location: "Conference Hall",
    description: "Expert sessions on career paths and higher education options.",
    featured: false,
  },
  {
    id: 4,
    title: "Inter-School Debate Competition",
    date: "15",
    month: "Feb",
    time: "9:00 AM - 4:00 PM",
    location: "Auditorium",
    description: "Students from various schools compete in an intellectual discourse.",
    featured: false,
  },
];

const EventsSection = () => {
  return (
    <section className="py-20 lg:py-28 bg-background">
      <div className="container">
        <div className="grid lg:grid-cols-5 gap-12">
          {/* Featured Event */}
          <div className="lg:col-span-2">
            <div className="inline-flex items-center gap-2 bg-secondary/10 text-secondary-foreground px-4 py-2 rounded-full mb-4">
              <span className="text-sm font-medium">Upcoming Events</span>
            </div>
            <h2 className="font-serif text-3xl lg:text-4xl font-bold text-primary mb-6">
              Don't Miss Out on Our Events at MIENEBI International School in Abuja
            </h2>
            <p className="text-muted-foreground mb-8">
              Stay connected with MIENEBI International School's vibrant community through various
              events, workshops, and celebrations throughout the year.
            </p>

            {/* Featured Event Card */}
            {events
              .filter((e) => e.featured)
              .map((event) => (
                <div
                  key={event.id}
                  className="bg-primary text-primary-foreground rounded-2xl p-6 relative overflow-hidden"
                >
                  {/* Decorative */}
                  <div className="absolute top-0 right-0 w-32 h-32 bg-secondary/20 rounded-full -translate-y-1/2 translate-x-1/2" />
                  
                  <div className="relative">
                    <div className="flex gap-4 mb-4">
                      <div className="bg-secondary text-secondary-foreground px-4 py-3 rounded-xl text-center">
                        <div className="font-serif text-3xl font-bold">{event.date}</div>
                        <div className="text-sm font-medium">{event.month}</div>
                      </div>
                      <div>
                        <span className="text-xs font-semibold bg-primary-foreground/20 px-3 py-1 rounded-full">
                          Featured
                        </span>
                        <h3 className="font-serif text-xl font-bold mt-2">
                          {event.title}
                        </h3>
                      </div>
                    </div>
                    <p className="text-primary-foreground/80 mb-4">
                      {event.description}
                    </p>
                    <div className="flex flex-wrap gap-4 text-sm text-primary-foreground/70">
                      <span className="flex items-center gap-1">
                        <Clock className="h-4 w-4" />
                        {event.time}
                      </span>
                      <span className="flex items-center gap-1">
                        <MapPin className="h-4 w-4" />
                        {event.location}
                      </span>
                    </div>
                  </div>
                </div>
              ))}
          </div>

          {/* Event List */}
          <div className="lg:col-span-3">
            <div className="space-y-4">
              {events
                .filter((e) => !e.featured)
                .map((event) => (
                  <div
                    key={event.id}
                    className="bg-card rounded-xl p-5 flex gap-4 shadow-card card-hover group"
                  >
                    {/* Date Box */}
                    <div className="bg-accent text-accent-foreground px-4 py-3 rounded-xl text-center shrink-0 group-hover:bg-secondary group-hover:text-secondary-foreground transition-colors">
                      <div className="font-serif text-2xl font-bold">{event.date}</div>
                      <div className="text-sm font-medium">{event.month}</div>
                    </div>

                    {/* Content */}
                    <div className="flex-1">
                      <h3 className="font-serif text-lg font-bold text-primary mb-2 group-hover:text-secondary transition-colors">
                        {event.title}
                      </h3>
                      <p className="text-muted-foreground text-sm mb-3 line-clamp-2">
                        {event.description}
                      </p>
                      <div className="flex flex-wrap gap-4 text-xs text-muted-foreground">
                        <span className="flex items-center gap-1">
                          <Clock className="h-3.5 w-3.5" />
                          {event.time}
                        </span>
                        <span className="flex items-center gap-1">
                          <MapPin className="h-3.5 w-3.5" />
                          {event.location}
                        </span>
                      </div>
                    </div>

                    {/* Arrow */}
                    <div className="hidden sm:flex items-center">
                      <div className="w-10 h-10 rounded-full bg-muted flex items-center justify-center group-hover:bg-secondary transition-colors">
                        <ArrowRight className="h-5 w-5 text-muted-foreground group-hover:text-secondary-foreground transition-colors" />
                      </div>
                    </div>
                  </div>
                ))}
            </div>

            <div className="mt-8">
              <Button
                asChild
                variant="outline"
                className="border-2 border-primary text-primary hover:bg-primary hover:text-primary-foreground font-semibold"
              >
                <Link to="/news">
                  View All Events
                  <ArrowRight className="ml-2 h-5 w-5" />
                </Link>
              </Button>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default EventsSection;
