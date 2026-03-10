import { useAppState } from "@/context/AppContext";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Switch } from "@/components/ui/switch";
import { Label } from "@/components/ui/label";
import { Slider } from "@/components/ui/slider";
import { useEffect } from "react";

const SettingsPage = () => {
  const { fontSize, setFontSize, darkMode, setDarkMode, notifications, setNotifications } = useAppState();

  useEffect(() => {
    document.documentElement.classList.toggle("dark", darkMode);
  }, [darkMode]);

  return (
    <div className="max-w-2xl mx-auto space-y-6 animate-fade-in">
      <h1 className="text-2xl font-bold gradient-text">Settings</h1>

      <Card className="glass-card">
        <CardHeader>
          <CardTitle className="text-base">Appearance</CardTitle>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <Label htmlFor="dark-mode">Dark Mode</Label>
            <Switch id="dark-mode" checked={darkMode} onCheckedChange={setDarkMode} />
          </div>

          <div className="space-y-3">
            <Label>Font Size: {fontSize}px</Label>
            <Slider
              value={[fontSize]}
              onValueChange={([v]) => setFontSize(v)}
              min={12}
              max={28}
              step={1}
              className="w-full"
            />
          </div>
        </CardContent>
      </Card>

      <Card className="glass-card">
        <CardHeader>
          <CardTitle className="text-base">Notifications</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-center justify-between">
            <Label htmlFor="notif">Enable Notifications</Label>
            <Switch id="notif" checked={notifications} onCheckedChange={setNotifications} />
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default SettingsPage;
