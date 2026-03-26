import { useAppState } from "./AppContext";
import { Card, CardContent, CardHeader, CardTitle } from "./card";
import { CheckCircle2, FileText, TrendingUp } from "lucide-react";
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid } from "recharts";

const Scores = () => {
  const { savedProofs } = useAppState();

  const totalProofs = savedProofs.length;
  const avgAccuracy = totalProofs > 0
    ? Math.round(savedProofs.reduce((total: number, proof) => total + proof.accuracy, 0) / totalProofs)
    : 0;
  const totalSteps = savedProofs.reduce((total: number, proof) => total + proof.steps.length, 0);

  const chartData = savedProofs.slice(0, 7).reverse().map((proof) => ({
    name: proof.theorem.slice(0, 15) + (proof.theorem.length > 15 ? "…" : ""),
    accuracy: proof.accuracy,
  }));

  return (
    <div className="max-w-4xl mx-auto space-y-6 animate-fade-in">
      <h1 className="text-2xl font-bold gradient-text">Scores & Progress</h1>

      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <Card className="glass-card hover-lift">
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">Avg Accuracy</CardTitle>
            <TrendingUp className="h-5 w-5 text-primary" />
          </CardHeader>
          <CardContent>
            <p className="text-3xl font-bold text-foreground">{avgAccuracy}%</p>
          </CardContent>
        </Card>

        <Card className="glass-card hover-lift">
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">Proofs Completed</CardTitle>
            <FileText className="h-5 w-5 text-accent" />
          </CardHeader>
          <CardContent>
            <p className="text-3xl font-bold text-foreground">{totalProofs}</p>
          </CardContent>
        </Card>

        <Card className="glass-card hover-lift">
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">Total Steps Written</CardTitle>
            <CheckCircle2 className="h-5 w-5 text-success" />
          </CardHeader>
          <CardContent>
            <p className="text-3xl font-bold text-foreground">{totalSteps}</p>
          </CardContent>
        </Card>
      </div>

      {chartData.length > 0 && (
        <Card className="glass-card p-4">
          <h3 className="font-semibold text-foreground mb-4">Recent Activity</h3>
          <ResponsiveContainer width="100%" height={260}>
            <BarChart data={chartData}>
              <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
              <XAxis dataKey="name" tick={{ fontSize: 11, fill: "hsl(var(--muted-foreground))" }} />
              <YAxis domain={[0, 100]} tick={{ fontSize: 11, fill: "hsl(var(--muted-foreground))" }} />
              <Tooltip
                contentStyle={{
                  backgroundColor: "hsl(var(--card))",
                  border: "1px solid hsl(var(--border))",
                  borderRadius: "8px",
                  fontSize: 12,
                }}
              />
              <Bar dataKey="accuracy" fill="hsl(var(--primary))" radius={[6, 6, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </Card>
      )}
    </div>
  );
};

export default Scores;
