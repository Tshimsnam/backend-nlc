import { Link } from "react-router-dom";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { AlertCircle, ArrowLeft, RefreshCw } from "lucide-react";

const PaymentCancelPage = () => {
  return (
    <main className="min-h-screen bg-secondary">
      <Header />

      <section className="pt-28 pb-16">
        <div className="container-custom max-w-2xl">
          <div className="text-center">
            {/* Icône d'annulation */}
            <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-orange-100 mb-6">
              <AlertCircle className="w-12 h-12 text-orange-600" />
            </div>

            {/* Message */}
            <h1 className="text-4xl font-bold mb-4">Paiement annulé</h1>
            <p className="text-muted-foreground text-lg mb-8">
              Vous avez annulé le processus de paiement. Votre inscription n'a pas été finalisée.
            </p>

            {/* Information */}
            <div className="bg-white rounded-3xl p-8 shadow-card mb-8 text-left">
              <h2 className="text-xl font-bold mb-4">Que s'est-il passé ?</h2>
              <p className="text-muted-foreground mb-4">
                Vous avez quitté la page de paiement avant de finaliser la transaction. 
                Aucun montant n'a été débité de votre compte.
              </p>
              <p className="text-muted-foreground">
                Votre inscription est toujours en attente. Vous pouvez reprendre le processus 
                de paiement à tout moment pour confirmer votre place à l'événement.
              </p>
            </div>

            {/* Actions */}
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button onClick={() => window.history.back()} size="lg" className="gap-2">
                <RefreshCw className="w-5 h-5" />
                Reprendre le paiement
              </Button>
              <Link to="/evenements">
                <Button variant="outline" size="lg" className="w-full sm:w-auto gap-2">
                  <ArrowLeft className="w-5 h-5" />
                  Retour aux événements
                </Button>
              </Link>
            </div>

            {/* Note */}
            <div className="mt-8 p-6 bg-blue-50 border border-blue-200 rounded-xl text-left">
              <h3 className="font-semibold mb-2 text-blue-900">À savoir</h3>
              <ul className="text-sm text-blue-800 space-y-2">
                <li>• Votre place n'est pas garantie tant que le paiement n'est pas confirmé</li>
                <li>• Les places sont limitées et attribuées selon l'ordre des paiements</li>
                <li>• Vous pouvez reprendre votre inscription à tout moment</li>
              </ul>
            </div>

            {/* Aide */}
            <div className="mt-6 p-6 bg-gray-50 border border-gray-200 rounded-xl text-left">
              <h3 className="font-semibold mb-2">Besoin d'aide ?</h3>
              <p className="text-sm text-muted-foreground mb-3">
                Si vous rencontrez des difficultés, notre équipe est là pour vous aider :
              </p>
              <div className="text-sm text-muted-foreground space-y-1">
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

export default PaymentCancelPage;
