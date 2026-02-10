// PARTIE 1: Imports et Types
import { useEffect, useState } from "react";
import { useParams, useLocation, Link } from "react-router-dom";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { ArrowLeft, ArrowRight, Printer, Download, CheckCircle } from "lucide-react";
import { QRCodeSVG } from 'qrcode.react';
import axios from "axios";

// Types
interface EventPrice {
  id: number;
  event_id: number;
  category: string;
  duration_type: string;
  amount: string;
  currency: string;
  label: string;
  description?: string;
}

interface Event {
  id: number;
  title: string;
  slug: string;
  description: string;
  date: string;
  time: string;
  location: string;
  image: string;
  event_prices: EventPrice[];
}

interface PaymentMode {
  id: string;
  label: string;
  description: string;
  requires_phone: boolean;
  sub_modes?: Array<{ id: string; label: string }>;
}

interface RegistrationFormData {
  event_price_id: number;
  full_name: string;
  email: string;
  phone: string;
  days: number;
  pay_type: string;
  pay_sub_type: string;
}

const API_URL = import.meta.env.VITE_API_URL;
