import React, { useState } from 'react';
import { useDocumentTitle } from '../hooks/useDocumentTitle';
import { Container } from '../components/ui/Container';
import { PageHeader } from '../components/layout/PageHeader';
import { Input } from '../components/ui/Input';
import { Textarea } from '../components/ui/Textarea';
import { Button } from '../components/ui/Button';
import { toast } from 'sonner';
import { Phone, Mail, MapPin, Send } from 'lucide-react';
import { useSiteSettings, setting } from '../hooks/useSiteSettings';

export default function Contact() {
  useDocumentTitle('Contact Support');
  const { settings } = useSiteSettings();
  const phone = setting(settings, 'contact_phone', null);
  const email = setting(settings, 'contact_email', null);
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({ name: '', phone: '', message: '' });

  const handleSubmit = (e) => {
    e.preventDefault();
    setLoading(true);
    setTimeout(() => {
      setLoading(false);
      toast.success('Your message has been received! Our support team will respond shortly.');
      setFormData({ name: '', phone: '', message: '' });
    }, 600);
  };

  return (
    <Container className="py-8 sm:py-12 max-w-4xl">
      <PageHeader
        title="Contact PetMart Support"
        subtitle="Have questions about listing your pet, verifying your account, or reporting an issue?"
        breadcrumbs={[{ label: 'Contact Us' }]}
      />

      <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
        {/* Contact Information */}
        <div className="md:col-span-1 space-y-4">
          <div className="bg-white p-5 rounded-2xl border border-border shadow-xs space-y-4">
            <h3 className="font-bold text-navy text-base">Direct Channels</h3>
            
            {phone && (
              <div className="flex items-start gap-3 text-sm">
                <Phone size={18} className="text-primary mt-0.5 shrink-0" />
                <div>
                  <p className="font-medium text-navy">Hotline</p>
                  <a href={`tel:${String(phone).replace(/[^\d+]/g, '')}`} className="text-muted hover:text-primary-hover">
                    {phone}
                  </a>
                </div>
              </div>
            )}

            {email && (
              <div className="flex items-start gap-3 text-sm">
                <Mail size={18} className="text-primary mt-0.5 shrink-0" />
                <div>
                  <p className="font-medium text-navy">Email Support</p>
                  <a href={`mailto:${email}`} className="break-all text-muted hover:text-primary-hover">
                    {email}
                  </a>
                </div>
              </div>
            )}

            <div className="flex items-start gap-3 text-sm">
              <MapPin size={18} className="text-primary mt-0.5 shrink-0" />
              <div>
                <p className="font-medium text-navy">Office</p>
                <p className="text-muted">Banani, Dhaka, Bangladesh</p>
              </div>
            </div>
          </div>
        </div>

        {/* Message Form */}
        <div className="md:col-span-2">
          <form onSubmit={handleSubmit} className="bg-white p-6 sm:p-8 rounded-2xl border border-border shadow-xs space-y-5">
            <h3 className="font-bold text-navy text-lg">Send Us a Message</h3>
            <Input
              label="Your Name"
              required
              placeholder="e.g. Arif Hossain"
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
            />
            <Input
              label="Contact Phone"
              required
              placeholder="01XXXXXXXXX"
              value={formData.phone}
              onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
            />
            <Textarea
              label="Message"
              required
              rows={4}
              placeholder="Describe your inquiry, issue, or feedback..."
              value={formData.message}
              onChange={(e) => setFormData({ ...formData, message: e.target.value })}
            />
            <Button type="submit" variant="primary" loading={loading} iconLeft={<Send size={16} />}>
              Send Message
            </Button>
          </form>
        </div>
      </div>
    </Container>
  );
}

