import { Link } from "react-router-dom";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { XCircle, ArrowLeft, RefreshCw } from "lucide-react";

const PaymentFailurePage = () => {
  return (
    <main className="min-h-screen bg-secondary">
      <Header />

      <section className="pt-28 pb-16">
        <div className="container-custom max-w-2xl">
          <div className="text-center">
            {/* Icône d'échec */}
            <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 mb-6">
              <XCircle className="w-12 h-12 text-red-600" />
            </div>

            {/* Message */}
            <h1 className="text-4xl font-bold mb-4">Paiement échoué</h1>
            <p className="text-muted-foreground text-lg mb-8">
              Votre paiement n'a pas pu être traité. Aucun montant n'a été débité de votre compte.
            </p>

            {/* Raisons possibles */}
            <div className="bg-white rounded-3xl p-8 shadow-card mb-8 text-left">
              <h2 className="text-xl font-bold mb-4">Raisons possibles :</h2>
              <ul className="space-y-3 text-muted-foreground">
                <li className="flex items-start gap-3">
                  <span className="text-red-500 mt-1">•</span>
                  <span>Solde insuffisant sur votre compte</span>
                </li>
                <li className="flex items-start gap-3">
                  <span className="text-red-500 mt-1">•</span>
                  <span>Informations de paiement incorrectes</span>
                </li>
                <li className="flex items-start gap-3">
                  <span className="text-red-500 mt-1">•</span>
                  <span>Problème de connexion avec votre banque ou opérateur mobile</span>
                </li>
                <li className="flex items-start gap-3">
                  <span className="text-red-500 mt-1">•</span>
                  <span>Transaction refusée par votre banque</span>
                </li>
              </ul>
            </div>

            {/* Actions */}
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button onClick={() => window.history.back()} size="lg" className="gap-2">
                <RefreshCw className="w-5 h-5" />
                Réessayer le paiement
              </Button>
              <Link to="/evenements">
                <Button variant="outline" size="lg" className="w-full sm:w-auto gap-2">
                  <ArrowLeft className="w-5 h-5" />
                  Retour aux événements
                </Button>
              </Link>
            </div>

            {/* Aide */}
            <div className="mt-8 p-6 bg-yellow-50 border border-yellow-200 rounded-xl text-left">
              <h3 className="font-semibold mb-2 text-yellow-900">Besoin d'aide ?</h3>
              <p className="text-sm text-yellow-800 mb-3">
                Si le problème persiste, veuillez contacter notre support :
              </p>
              <div className="text-sm text-yellow-800 space-y-1">
                <p>📧 Email : support@nlc-rdc.org</p>
                <p>📞 Téléphone : +243 XXX XXX XXX</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <Footer />
    </main>
  );
};

export default PaymentFailurePage;
