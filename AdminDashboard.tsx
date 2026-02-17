import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import axios from "axios";
import {
  LayoutDashboard,
  Ticket,
  Users,
  Calendar,
  CheckCircle,
  Clock,
  DollarSign,
  Menu,
  X,
  Search,
  Filter,
  LogOut,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

const API_URL = import.meta.env.VITE_API_URL;

interface DashboardStats {
  total_tickets: number;
  tickets_pending: number;
  tickets_completed: number;
  tickets_failed: number;
  total_revenue: number;
  total_events: number;
  active_events: number;
  total_users: number;
}

interface TicketItem {
  id: number;
  reference: string;
  full_name: string;
  email: string;
  phone: string;
  amount: number;
  currency: string;
  payment_status: string;
  created_at: string;
  event: {
    title: string;
  };
}

interface EventStats {
  id: number;
  title: string;
  date: string;
  location: string;
  tickets_count: number;
  tickets?: Array<{ total_revenue: number }>;
}

interface UserItem {
  id: number;
  name: string;
  email: string;
  created_at: string;
  roles: Array<{ name: string }>;
}

const AdminDashboard = () => {
  const navigate = useNavigate();
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [recentTickets, setRecentTickets] = useState<TicketItem[]>([]);
  const [allTickets, setAllTickets] = useState<TicketItem[]>([]);
  const [events, setEvents] = useState<EventStats[]>([]);
  const [users, setUsers] = useState<UserItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [activeTab, setActiveTab] = useState("dashboard");
  const [searchTerm, setSearchTerm] = useState("");
  const [filterStatus, setFilterStatus] = useState("all");

  useEffect(() => {
    checkAuth();
    fetchDashboardData();
  }, []);

  useEffect(() => {
    if (activeTab === "tickets") {
      fetchPendingTickets();
    } else if (activeTab === "events") {
      fetchEventsStats();
    } else if (activeTab === "users") {
      fetchUsers();
    }
  }, [activeTab]);

  const checkAuth = () => {
    const token = localStorage.getItem("auth_token");
    if (!token) {
      navigate("/login");
    }
  };

  const handleLogout = async () => {
    try {
      const token = localStorage.getItem("auth_token");
      await axios.post(`${API_URL.replace('/api', '')}/logout`, {}, {
        headers: { Authorization: `Bearer ${token}` }
      });
    } catch (error) {
      console.error("Erreur lors de la déconnexion:", error);
    } finally {
      localStorage.removeItem("auth_token");
      navigate("/login");
    }
  };

  const fetchDashboardData = async () => {
    try {
      const token = localStorage.getItem("auth_token");
      const response = await axios.get(`${API_URL.replace('/api', '')}/admin/dashboard`, {
        headers: { Authorization: `Bearer ${token}` },
      });

      setStats(response.data.stats);
      setRecentTickets(response.data.recent_tickets);
    } catch (error) {
      console.error("Erreur:", error);
      if (axios.isAxiosError(error) && error.response?.status === 401) {
        navigate("/login");
      }
    } finally {
      setLoading(false);
    }
  };

  const fetchPendingTickets = async () => {
    try {
      const token = localStorage.getItem("auth_token");
      const response = await axios.get(`${API_URL.replace('/api', '')}/admin/tickets/pending`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setAllTickets(response.data.data || []);
    } catch (error) {
      console.error("Erreur:", error);
    }
  };

  const fetchEventsStats = async () => {
    try {
      const token = localStorage.getItem("auth_token");
      const response = await axios.get(`${API_URL.replace('/api', '')}/admin/events/stats`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setEvents(response.data || []);
    } catch (error) {
      console.error("Erreur:", error);
    }
  };

  const fetchUsers = async () => {
    try {
      const token = localStorage.getItem("auth_token");
      const response = await axios.get(`${API_URL.replace('/api', '')}/admin/users`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setUsers(response.data.data || []);
    } catch (error) {
      console.error("Erreur:", error);
    }
  };

  const handleValidateTicket = async (reference: string) => {
    try {
      const token = localStorage.getItem("auth_token");
      await axios.post(
        `${API_URL.replace('/api', '')}/admin/tickets/${reference}/validate`,
        {},
        { headers: { Authorization: `Bearer ${token}` } }
      );
      
      // Refresh data
      fetchDashboardData();
      if (activeTab === "tickets") {
        fetchPendingTickets();
      }
      
      alert("Ticket validé avec succès!");
    } catch (error) {
      console.error("Erreur:", error);
      alert("Erreur lors de la validation du ticket");
    }
  };

  const filteredTickets = allTickets.filter((ticket) => {
    const matchesSearch =
      ticket.reference.toLowerCase().includes(searchTerm.toLowerCase()) ||
      ticket.full_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      ticket.email.toLowerCase().includes(searchTerm.toLowerCase());

    const matchesFilter =
      filterStatus === "all" || ticket.payment_status === filterStatus;

    return matchesSearch && matchesFilter;
  });

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 flex">
      {/* Sidebar */}
      <aside
        className={`${
          sidebarOpen ? "w-64" : "w-20"
        } bg-white border-r border-gray-200 transition-all duration-300 fixed h-full z-10 flex flex-col`}
      >
        <div className="p-4 border-b border-gray-200 flex items-center justify-between">
          {sidebarOpen && <h1 className="text-xl font-bold text-gray-800">Admin Panel</h1>}
          <button
            onClick={() => setSidebarOpen(!sidebarOpen)}
            className="p-2 hover:bg-gray-100 rounded-lg"
          >
            {sidebarOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </button>
        </div>

        <nav className="p-4 space-y-2 flex-1">
          <button
            onClick={() => setActiveTab("dashboard")}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors ${
              activeTab === "dashboard"
                ? "bg-blue-50 text-blue-600"
                : "text-gray-600 hover:bg-gray-50"
            }`}
          >
            <LayoutDashboard className="w-5 h-5" />
            {sidebarOpen && <span className="font-medium">Dashboard</span>}
          </button>

          <button
            onClick={() => setActiveTab("tickets")}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors ${
              activeTab === "tickets"
                ? "bg-blue-50 text-blue-600"
                : "text-gray-600 hover:bg-gray-50"
            }`}
          >
            <Ticket className="w-5 h-5" />
            {sidebarOpen && <span className="font-medium">Tickets</span>}
          </button>

          <button
            onClick={() => setActiveTab("events")}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors ${
              activeTab === "events"
                ? "bg-blue-50 text-blue-600"
                : "text-gray-600 hover:bg-gray-50"
            }`}
          >
            <Calendar className="w-5 h-5" />
            {sidebarOpen && <span className="font-medium">Événements</span>}
          </button>

          <button
            onClick={() => setActiveTab("users")}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors ${
              activeTab === "users"
                ? "bg-blue-50 text-blue-600"
                : "text-gray-600 hover:bg-gray-50"
            }`}
          >
            <Users className="w-5 h-5" />
            {sidebarOpen && <span className="font-medium">Utilisateurs</span>}
          </button>
        </nav>

        <div className="p-4 border-t border-gray-200">
          <button
            onClick={handleLogout}
            className="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition-colors"
          >
            <LogOut className="w-5 h-5" />
            {sidebarOpen && <span className="font-medium">Déconnexion</span>}
          </button>
        </div>
      </aside>

      {/* Main Content */}
      <main className={`flex-1 ${sidebarOpen ? "ml-64" : "ml-20"} transition-all duration-300`}>
        <div className="p-8">
          {/* Dashboard Tab */}
          {activeTab === "dashboard" && (
            <>
              {/* Header */}
              <div className="mb-8">
                <h2 className="text-3xl font-bold text-gray-900">Dashboard</h2>
                <p className="text-gray-600 mt-1">Vue d'ensemble de votre plateforme</p>
              </div>

              {/* Stats Cards */}
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                  <div className="flex items-center justify-between mb-4">
                    <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                      <Ticket className="w-6 h-6 text-blue-600" />
                    </div>
                    <span className="text-sm text-gray-500">Total</span>
                  </div>
                  <h3 className="text-3xl font-bold text-gray-900">{stats?.total_tickets}</h3>
                  <p className="text-sm text-gray-600 mt-1">Tickets créés</p>
                </div>

                <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                  <div className="flex items-center justify-between mb-4">
                    <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                      <CheckCircle className="w-6 h-6 text-green-600" />
                    </div>
                    <span className="text-sm text-gray-500">Validés</span>
                  </div>
                  <h3 className="text-3xl font-bold text-gray-900">{stats?.tickets_completed}</h3>
                  <p className="text-sm text-gray-600 mt-1">Paiements confirmés</p>
                </div>

                <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                  <div className="flex items-center justify-between mb-4">
                    <div className="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                      <Clock className="w-6 h-6 text-orange-600" />
                    </div>
                    <span className="text-sm text-gray-500">En attente</span>
                  </div>
                  <h3 className="text-3xl font-bold text-gray-900">{stats?.tickets_pending}</h3>
                  <p className="text-sm text-gray-600 mt-1">À valider</p>
                </div>

                <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                  <div className="flex items-center justify-between mb-4">
                    <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                      <DollarSign className="w-6 h-6 text-purple-600" />
                    </div>
                    <span className="text-sm text-gray-500">Revenus</span>
                  </div>
                  <h3 className="text-3xl font-bold text-gray-900">${stats?.total_revenue}</h3>
                  <p className="text-sm text-gray-600 mt-1">Total encaissé</p>
                </div>
              </div>

              {/* Recent Tickets */}
              <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                <div className="p-6 border-b border-gray-200">
                  <h3 className="text-xl font-bold text-gray-900">Tickets récents</h3>
                </div>
                <div className="overflow-x-auto">
                  <table className="w-full">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Référence
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Participant
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Événement
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Montant
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Statut
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Actions
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200">
                      {recentTickets.map((ticket) => (
                        <tr key={ticket.id} className="hover:bg-gray-50">
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span className="font-mono text-sm font-medium text-gray-900">
                              {ticket.reference}
                            </span>
                          </td>
                          <td className="px-6 py-4">
                            <div>
                              <div className="text-sm font-medium text-gray-900">{ticket.full_name}</div>
                              <div className="text-sm text-gray-500">{ticket.email}</div>
                            </div>
                          </td>
                          <td className="px-6 py-4">
                            <span className="text-sm text-gray-900">{ticket.event.title}</span>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span className="text-sm font-medium text-gray-900">
                              {ticket.amount} {ticket.currency}
                            </span>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span
                              className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                ticket.payment_status === "completed"
                                  ? "bg-green-100 text-green-800"
                                  : ticket.payment_status === "pending_cash"
                                  ? "bg-orange-100 text-orange-800"
                                  : "bg-red-100 text-red-800"
                              }`}
                            >
                              {ticket.payment_status === "completed"
                                ? "Validé"
                                : ticket.payment_status === "pending_cash"
                                ? "En attente"
                                : "Échoué"}
                            </span>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            {ticket.payment_status === "pending_cash" && (
                              <Button
                                size="sm"
                                onClick={() => handleValidateTicket(ticket.reference)}
                                className="bg-green-600 hover:bg-green-700"
                              >
                                Valider
                              </Button>
                            )}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </>
          )}

          {/* Tickets Tab */}
          {activeTab === "tickets" && (
            <>
              <div className="mb-8">
                <h2 className="text-3xl font-bold text-gray-900">Gestion des Tickets</h2>
                <p className="text-gray-600 mt-1">Liste complète des tickets en attente</p>
              </div>

              {/* Filters */}
              <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-200 mb-6">
                <div className="flex flex-col md:flex-row gap-4">
                  <div className="flex-1">
                    <div className="relative">
                      <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                      <Input
                        type="text"
                        placeholder="Rechercher par référence, nom, email..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="pl-10"
                      />
                    </div>
                  </div>
                  <div className="flex gap-2">
                    <Button
                      variant={filterStatus === "all" ? "default" : "outline"}
                      onClick={() => setFilterStatus("all")}
                    >
                      Tous
                    </Button>
                    <Button
                      variant={filterStatus === "pending_cash" ? "default" : "outline"}
                      onClick={() => setFilterStatus("pending_cash")}
                    >
                      En attente
                    </Button>
                    <Button
                      variant={filterStatus === "completed" ? "default" : "outline"}
                      onClick={() => setFilterStatus("completed")}
                    >
                      Validés
                    </Button>
                  </div>
                </div>
              </div>

              {/* Tickets Table */}
              <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                <div className="overflow-x-auto">
                  <table className="w-full">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Référence
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Participant
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Contact
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Événement
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Montant
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Statut
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Actions
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200">
                      {filteredTickets.map((ticket) => (
                        <tr key={ticket.id} className="hover:bg-gray-50">
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span className="font-mono text-sm font-medium text-gray-900">
                              {ticket.reference}
                            </span>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span className="text-sm font-medium text-gray-900">{ticket.full_name}</span>
                          </td>
                          <td className="px-6 py-4">
                            <div className="text-sm text-gray-900">{ticket.email}</div>
                            <div className="text-sm text-gray-500">{ticket.phone}</div>
                          </td>
                          <td className="px-6 py-4">
                            <span className="text-sm text-gray-900">{ticket.event.title}</span>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span className="text-sm font-medium text-gray-900">
                              {ticket.amount} {ticket.currency}
                            </span>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span
                              className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                ticket.payment_status === "completed"
                                  ? "bg-green-100 text-green-800"
                                  : ticket.payment_status === "pending_cash"
                                  ? "bg-orange-100 text-orange-800"
                                  : "bg-red-100 text-red-800"
                              }`}
                            >
                              {ticket.payment_status === "completed"
                                ? "Validé"
                                : ticket.payment_status === "pending_cash"
                                ? "En attente"
                                : "Échoué"}
                            </span>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            {ticket.payment_status === "pending_cash" && (
                              <Button
                                size="sm"
                                onClick={() => handleValidateTicket(ticket.reference)}
                                className="bg-green-600 hover:bg-green-700"
                              >
                                Valider
                              </Button>
                            )}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                  {filteredTickets.length === 0 && (
                    <div className="text-center py-12">
                      <p className="text-gray-500">Aucun ticket trouvé</p>
                    </div>
                  )}
                </div>
              </div>
            </>
          )}

          {/* Events Tab */}
          {activeTab === "events" && (
            <>
              <div className="mb-8">
                <h2 className="text-3xl font-bold text-gray-900">Statistiques des Événements</h2>
                <p className="text-gray-600 mt-1">Performance de vos événements</p>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {events.map((event) => (
                  <div key={event.id} className="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                    <h3 className="text-lg font-bold text-gray-900 mb-2">{event.title}</h3>
                    <div className="space-y-2 text-sm text-gray-600">
                      <p>📅 {new Date(event.date).toLocaleDateString("fr-FR")}</p>
                      <p>📍 {event.location}</p>
                      <div className="pt-4 border-t border-gray-200 mt-4">
                        <div className="flex justify-between items-center mb-2">
                          <span className="text-gray-600">Tickets vendus</span>
                          <span className="font-bold text-gray-900">{event.tickets_count}</span>
                        </div>
                        <div className="flex justify-between items-center">
                          <span className="text-gray-600">Revenus</span>
                          <span className="font-bold text-green-600">
                            ${event.tickets?.[0]?.total_revenue || 0}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
              {events.length === 0 && (
                <div className="bg-white rounded-xl p-12 shadow-sm border border-gray-200 text-center">
                  <p className="text-gray-500">Aucun événement trouvé</p>
                </div>
              )}
            </>
          )}

          {/* Users Tab */}
          {activeTab === "users" && (
            <>
              <div className="mb-8">
                <h2 className="text-3xl font-bold text-gray-900">Gestion des Utilisateurs</h2>
                <p className="text-gray-600 mt-1">Liste des utilisateurs validateurs</p>
              </div>

              <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                <div className="overflow-x-auto">
                  <table className="w-full">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Nom
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Email
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Rôle
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Date d'inscription
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200">
                      {users.map((user) => (
                        <tr key={user.id} className="hover:bg-gray-50">
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span className="text-sm font-medium text-gray-900">{user.name}</span>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span className="text-sm text-gray-900">{user.email}</span>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span className="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                              {user.roles?.[0]?.name || "Utilisateur"}
                            </span>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span className="text-sm text-gray-500">
                              {new Date(user.created_at).toLocaleDateString("fr-FR")}
                            </span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                  {users.length === 0 && (
                    <div className="text-center py-12">
                      <p className="text-gray-500">Aucun utilisateur trouvé</p>
                    </div>
                  )}
                </div>
              </div>
            </>
          )}
        </div>
      </main>
    </div>
  );
};

export default AdminDashboard;
