import React, { Suspense, lazy } from 'react';
import { Routes, Route } from 'react-router-dom';
import { Toaster } from 'sonner';

import Navbar from './components/layout/Navbar';
import Footer from './components/layout/Footer';
import ScrollToTop from './components/layout/ScrollToTop';
import { PageSkeleton } from './components/ui/Skeleton';
import { ProtectedRoute } from './routes/ProtectedRoute';
import { GuestRoute } from './routes/GuestRoute';

// Lazy loaded page components
const Home = lazy(() => import('./pages/Home'));
const CategoryBrowse = lazy(() => import('./pages/CategoryBrowse'));
const ProductDetail = lazy(() => import('./pages/ProductDetail'));
const CreateListing = lazy(() => import('./pages/CreateListing'));
const Login = lazy(() => import('./pages/Login'));
const Register = lazy(() => import('./pages/Register'));
const ForgotPassword = lazy(() => import('./pages/ForgotPassword'));
const SellerDashboard = lazy(() => import('./pages/SellerDashboard'));
const Wishlist = lazy(() => import('./pages/Wishlist'));
const Inquiries = lazy(() => import('./pages/Inquiries'));
const About = lazy(() => import('./pages/About'));
const Contact = lazy(() => import('./pages/Contact'));
const SafetyTips = lazy(() => import('./pages/SafetyTips'));
const Terms = lazy(() => import('./pages/Terms'));
const Privacy = lazy(() => import('./pages/Privacy'));
const NotFound = lazy(() => import('./pages/NotFound'));
const UiKit = lazy(() => import('./pages/UiKit'));

function App() {
  return (
    <div className="flex flex-col min-h-screen bg-background">
      <Toaster richColors position="top-right" />
      <ScrollToTop />
      <Navbar />

      <main className="flex-1">
        <Suspense fallback={<PageSkeleton />}>
          <Routes>
            {/* Public Marketplace Routes */}
            <Route path="/" element={<Home />} />
            <Route path="/browse" element={<CategoryBrowse />} />
            <Route path="/product/:identifier" element={<ProductDetail />} />

            {/* Informational Pages */}
            <Route path="/about" element={<About />} />
            <Route path="/contact" element={<Contact />} />
            <Route path="/safety" element={<SafetyTips />} />
            <Route path="/terms" element={<Terms />} />
            <Route path="/privacy" element={<Privacy />} />

            {/* Guest Only Routes (guarded against logged-in users) */}
            <Route
              path="/login"
              element={
                <GuestRoute>
                  <Login />
                </GuestRoute>
              }
            />
            <Route
              path="/register"
              element={
                <GuestRoute>
                  <Register />
                </GuestRoute>
              }
            />
            <Route
              path="/forgot-password"
              element={
                <GuestRoute>
                  <ForgotPassword />
                </GuestRoute>
              }
            />

            {/* Protected Routes (guarded against logged-out visitors) */}
            <Route
              path="/dashboard"
              element={
                <ProtectedRoute>
                  <SellerDashboard />
                </ProtectedRoute>
              }
            />
            <Route
              path="/create-listing"
              element={
                <ProtectedRoute>
                  <CreateListing />
                </ProtectedRoute>
              }
            />
            <Route
              path="/wishlist"
              element={
                <ProtectedRoute>
                  <Wishlist />
                </ProtectedRoute>
              }
            />
            <Route
              path="/inquiries"
              element={
                <ProtectedRoute>
                  <Inquiries />
                </ProtectedRoute>
              }
            />

            {/* Dev Only Route */}
            {import.meta.env.DEV && <Route path="/ui-kit" element={<UiKit />} />}

            {/* Catch-all 404 Route */}
            <Route path="*" element={<NotFound />} />
          </Routes>
        </Suspense>
      </main>

      <Footer />
    </div>
  );
}

export default App;
