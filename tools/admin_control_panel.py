import subprocess
import sys
import threading
import tkinter as tk
from tkinter import scrolledtext, messagebox
from tkinter import ttk
import os
import webbrowser
import socket
import time

# Configure paths
REPO_ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
PHP_ARTISAN = os.path.join(REPO_ROOT, "artisan")
PYTHON_EXE = sys.executable or "python"
# Try to locate cloudflared; allow override via env var
CLOUDFLARED_EXE = os.environ.get("CLOUDFLARED_EXE")
if not CLOUDFLARED_EXE:
    _cf_default = os.path.join("C:\\", "cloudflared", "cloudflared.exe")
    CLOUDFLARED_EXE = _cf_default if os.path.exists(_cf_default) else "cloudflared"

# XAMPP locations (override with XAMPP_DIR env if needed)
XAMPP_DIR = os.environ.get("XAMPP_DIR", r"C:\\xampp")
APACHE_START_BAT = os.path.join(XAMPP_DIR, "apache_start.bat")
MYSQL_START_BAT = os.path.join(XAMPP_DIR, "mysql_start.bat")
# Stop batch files (if available)
APACHE_STOP_BAT = os.path.join(XAMPP_DIR, "apache_stop.bat")
MYSQL_STOP_BAT = os.path.join(XAMPP_DIR, "mysql_stop.bat")

COMMANDS = {
    "Sync Resolved to MySQL": ["php", "artisan", "sync:incident-logs"],
    "Predict Severity (Mobile)": ["php", "artisan", "incidents:predict-severity"],
    # Long-running dev servers/tools
    "Start PHP Server (serve)": [
        "php",
        "artisan",
        "serve",
        "--host=127.0.0.1",
        "--port=8000",
    ],
    "Start Severity API (python)": [PYTHON_EXE, "predict_severity_api.py"],
}

# Labels considered long-running dev tasks; we disable only their own button
LONG_RUNNING_LABELS = {
    "Start PHP Server (serve)",
    "Start Severity API (python)",
    "Start Cloudflare Tunnel",
}


class AdminControlPanel(tk.Tk):
    def __init__(self):
        super().__init__()
        self.title("CAP102 Admin Control Panel")
        self.geometry("750x550")
        self.minsize(600, 450)
        self.running = False
        self.current_process = None
        self.current_label = None
        self.command_buttons = []
        self.buttons = {}
        self.processes = {}  # label -> Popen
        # Auto Pilot state
        self.autopilot_enabled = False
        self.autopilot_interval_ms = 5000
        self.autopilot_after_id = None
        self.autopilot_btn = None
        self.xampp_btn = None
        # Track server buttons active state: label -> bool
        self.server_states = {}
        # Busy guard for XAMPP to avoid disabled white flash
        self.xampp_busy = False

        # Set window icon and background
        self.configure(bg="#f0f0f0")

        # Apply enhanced ttk theme and styles
        try:
            style = ttk.Style(self)
            # Prefer Windows native theme
            available_themes = style.theme_names()
            preferred_themes = [
                "winnative",
                "vista",
                "xpnative",
                "clam",
                "alt",
                "default",
            ]
            for theme in preferred_themes:
                if theme in available_themes:
                    style.theme_use(theme)
                    break

            # Enhanced styling
            style.configure(
                "Header.TLabel",
                font=("Segoe UI", 20, "bold"),
                foreground="#2c3e50",
                background="#f0f0f0",
            )
            style.configure(
                "Subheader.TLabel",
                font=("Segoe UI", 11),
                foreground="#7f8c8d",
                background="#f0f0f0",
            )

            # Enhanced button styles
            style.configure(
                "Action.TButton",
                font=("Segoe UI", 10, "bold"),
                padding=(15, 8),
                relief="flat",
            )
            style.configure(
                "LongRunning.TButton",
                font=("Segoe UI", 10, "bold"),
                padding=(15, 8),
                relief="flat",
            )
            style.configure(
                "Control.TButton", font=("Segoe UI", 9), padding=(12, 6), relief="flat"
            )
            style.configure(
                "Status.TLabel",
                font=("Segoe UI", 9),
                foreground="#34495e",
                background="#ecf0f1",
            )

            # Map button colors
            style.map(
                "Action.TButton",
                background=[("active", "#3498db"), ("pressed", "#2980b9")],
                foreground=[("active", "white"), ("pressed", "white")],
            )
            style.map(
                "LongRunning.TButton",
                background=[("active", "#e74c3c"), ("pressed", "#c0392b")],
                foreground=[("active", "white"), ("pressed", "white")],
            )
            style.map(
                "Control.TButton",
                background=[("active", "#95a5a6"), ("pressed", "#7f8c8d")],
                foreground=[("active", "white"), ("pressed", "white")],
            )

        except Exception:
            pass
        self._build_ui()

    def _build_ui(self):
        # Main container with gradient-like background
        main_container = tk.Frame(self, bg="#ecf0f1", relief="flat", bd=0)
        main_container.pack(fill=tk.BOTH, expand=True, padx=1, pady=1)

        # Compact header section
        header_frame = tk.Frame(main_container, bg="#34495e", height=60)
        header_frame.pack(fill=tk.X, pady=(0, 1))
        header_frame.pack_propagate(False)

        # Header content
        header_content = tk.Frame(header_frame, bg="#34495e")
        header_content.pack(fill=tk.BOTH, expand=True, padx=12, pady=8)

        header = tk.Label(
            header_content,
            text="CAP102 Admin Panel",
            font=("Segoe UI", 14, "bold"),
            fg="white",
            bg="#34495e",
        )
        header.pack(anchor=tk.W)

        subheader = tk.Label(
            header_content,
            text="Maintenance & Development Tools",
            font=("Segoe UI", 9),
            fg="#bdc3c7",
            bg="#34495e",
        )
        subheader.pack(anchor=tk.W, pady=(1, 0))

        # Content container
        content_frame = tk.Frame(main_container, bg="#ecf0f1")
        content_frame.pack(fill=tk.BOTH, expand=True, padx=8, pady=(8, 0))

        # Compact action buttons section - use grid for better space utilization
        actions_frame = tk.LabelFrame(
            content_frame,
            text=" Actions ",
            font=("Segoe UI", 10, "bold"),
            fg="#2c3e50",
            bg="#ecf0f1",
            relief="flat",
            bd=1,
        )
        actions_frame.pack(fill=tk.X, pady=(0, 8))

        # Buttons container with responsive grid layout
        buttons_container = tk.Frame(actions_frame, bg="#ecf0f1")
        buttons_container.pack(fill=tk.X, padx=8, pady=8)

        # Configure grid weights for responsiveness
        buttons_container.grid_columnconfigure(0, weight=1)
        buttons_container.grid_columnconfigure(1, weight=1)

        # Separate buttons by type for better organization
        sync_buttons = []
        server_buttons = []

        for label, cmd in COMMANDS.items():
            if "Sync" in label or "Predict" in label:
                sync_buttons.append((label, cmd))
            else:
                server_buttons.append((label, cmd))

        # Data operations row - responsive grid
        if sync_buttons:
            data_label = tk.Label(
                buttons_container,
                text="Data:",
                font=("Segoe UI", 9, "bold"),
                fg="#2c3e50",
                bg="#ecf0f1",
            )
            data_label.grid(row=0, column=0, columnspan=2, sticky="w", pady=(0, 3))

            for i, (label, cmd) in enumerate(sync_buttons):
                # Compact button text for smaller windows
                short_text = label.replace("Resolved to MySQL", "to MySQL").replace(
                    "Severity (Mobile)", "Severity"
                )

                btn = tk.Button(
                    buttons_container,
                    text=short_text,
                    font=("Segoe UI", 9, "bold"),
                    bg="#3498db",
                    fg="white",
                    relief="flat",
                    bd=0,
                    padx=12,
                    pady=6,
                    cursor="hand2",
                    command=lambda l=label, c=cmd: self.run_command(l, c),
                )
                btn.grid(row=1, column=i, sticky="ew", padx=(0, 4 if i == 0 else 0))

                # Hover effects
                def on_enter(e, btn=btn):
                    btn.config(bg="#2980b9")

                def on_leave(e, btn=btn):
                    btn.config(bg="#3498db")

                btn.bind("<Enter>", on_enter)
                btn.bind("<Leave>", on_leave)

                self.command_buttons.append(btn)
                self.buttons[label] = btn

        # Server operations row
        if server_buttons:
            server_label = tk.Label(
                buttons_container,
                text="Servers:",
                font=("Segoe UI", 9, "bold"),
                fg="#2c3e50",
                bg="#ecf0f1",
            )
            server_label.grid(row=2, column=0, columnspan=2, sticky="w", pady=(8, 3))

            for i, (label, cmd) in enumerate(server_buttons):
                # Compact button text
                short_text = (
                    label.replace("Start ", "")
                    .replace(" (serve)", "")
                    .replace(" (python)", "")
                )

                btn = tk.Button(
                    buttons_container,
                    text=short_text,
                    font=("Segoe UI", 9, "bold"),
                    bg="#3498db",
                    fg="white",
                    relief="flat",
                    bd=0,
                    padx=12,
                    pady=6,
                    cursor="hand2",
                    command=lambda l=label, c=cmd: self.run_command(l, c),
                )
                # Add right gap for first column and bottom gap to separate rows
                btn.grid(
                    row=3,
                    column=i,
                    sticky="ew",
                    padx=(0, 4 if i == 0 else 0),
                    pady=(0, 6),
                )

                # Initialize server active state and hover effects aware of state
                self.server_states[label] = False

                def on_enter(e, lbl=label, b=btn):
                    try:
                        b.config(
                            bg=("#27ae60" if self.server_states.get(lbl) else "#2980b9")
                        )
                    except Exception:
                        pass

                def on_leave(e, lbl=label, b=btn):
                    try:
                        b.config(
                            bg=("#2ecc71" if self.server_states.get(lbl) else "#3498db")
                        )
                    except Exception:
                        pass

                btn.bind("<Enter>", on_enter)
                btn.bind("<Leave>", on_leave)

                self.command_buttons.append(btn)
                self.buttons[label] = btn

            # Tunnel button (Cloudflare) - second row
            tunnel_btn = tk.Button(
                buttons_container,
                text="Tunnel",
                font=("Segoe UI", 9, "bold"),
                bg="#3498db",
                fg="white",
                relief="flat",
                bd=0,
                padx=12,
                pady=6,
                cursor="hand2",
                command=self.start_tunnel,
            )
            tunnel_btn.grid(row=4, column=0, sticky="ew", padx=(0, 4))
            # Track state for tunnel using its long-running label
            self.server_states["Start Cloudflare Tunnel"] = False

            def tunnel_enter(e, lbl="Start Cloudflare Tunnel", b=tunnel_btn):
                try:
                    b.config(
                        bg=("#27ae60" if self.server_states.get(lbl) else "#2980b9")
                    )
                except Exception:
                    pass

            def tunnel_leave(e, lbl="Start Cloudflare Tunnel", b=tunnel_btn):
                try:
                    b.config(
                        bg=("#2ecc71" if self.server_states.get(lbl) else "#3498db")
                    )
                except Exception:
                    pass

            tunnel_btn.bind("<Enter>", tunnel_enter)
            tunnel_btn.bind("<Leave>", tunnel_leave)
            # store reference for state toggling in other methods and map to label for run_command
            self.tunnel_btn = tunnel_btn
            self.buttons["Start Cloudflare Tunnel"] = tunnel_btn

            # XAMPP button (Apache + MySQL) - second row
            xampp_btn = tk.Button(
                buttons_container,
                text="XAMPP",
                font=("Segoe UI", 9, "bold"),
                bg="#3498db",
                fg="white",
                relief="flat",
                bd=0,
                padx=12,
                pady=6,
                cursor="hand2",
                command=self.toggle_xampp,
            )
            xampp_btn.grid(row=4, column=1, sticky="ew", padx=(0, 0))
            # Track XAMPP state separately
            self.server_states["XAMPP"] = False

            def xampp_enter(e, lbl="XAMPP", b=xampp_btn):
                try:
                    b.config(
                        bg=("#27ae60" if self.server_states.get(lbl) else "#2980b9")
                    )
                except Exception:
                    pass

            def xampp_leave(e, lbl="XAMPP", b=xampp_btn):
                try:
                    b.config(
                        bg=("#2ecc71" if self.server_states.get(lbl) else "#3498db")
                    )
                except Exception:
                    pass

            xampp_btn.bind("<Enter>", xampp_enter)
            xampp_btn.bind("<Leave>", xampp_leave)
            # store reference for state toggling in other methods
            self.xampp_btn = xampp_btn
            # Also store in buttons dict for consistency if needed
            self.buttons["XAMPP"] = xampp_btn

        # Compact output section with controlled height
        output_frame = tk.LabelFrame(
            content_frame,
            text=" Console ",
            font=("Segoe UI", 10, "bold"),
            fg="#2c3e50",
            bg="#ecf0f1",
            relief="flat",
            bd=1,
        )
        output_frame.pack(fill=tk.BOTH, expand=True, pady=(0, 6))

        # Set a reasonable minimum height for console to ensure status bar is visible
        output_frame.update_idletasks()

        # Output area with enhanced styling - controlled expansion
        output_container = tk.Frame(output_frame, bg="#2c3e50", relief="sunken", bd=1)
        output_container.pack(fill=tk.BOTH, expand=True, padx=6, pady=6)

        self.output = scrolledtext.ScrolledText(
            output_container,
            wrap=tk.WORD,
            state=tk.DISABLED,
            font=("Consolas", 9),
            bg="#2c3e50",
            fg="#ecf0f1",
            insertbackground="#ecf0f1",
            selectbackground="#34495e",
            relief="flat",
            bd=0,
            height=12,
        )  # Set explicit height to control space
        self.output.pack(fill=tk.BOTH, expand=True, padx=1, pady=1)

        # Compact status bar - always visible at bottom
        statusbar = tk.Frame(
            content_frame, bg="#bdc3c7", height=34, relief="flat", bd=1
        )
        statusbar.pack(fill=tk.X, side=tk.BOTTOM)  # Force to bottom
        statusbar.pack_propagate(False)

        # Status content
        status_content = tk.Frame(statusbar, bg="#bdc3c7")
        status_content.pack(fill=tk.BOTH, expand=True, padx=8, pady=4)

        # Left side - compact progress and status
        left_status = tk.Frame(status_content, bg="#bdc3c7")
        left_status.pack(side=tk.LEFT, fill=tk.Y)

        self.progress = ttk.Progressbar(left_status, mode="indeterminate", length=80)
        self.progress.pack(side=tk.LEFT, pady=1)

        self.status_label = tk.Label(
            left_status,
            text="Ready",
            font=("Segoe UI", 8, "bold"),
            fg="#2c3e50",
            bg="#bdc3c7",
        )
        self.status_label.pack(side=tk.LEFT, padx=(6, 0), pady=1)

        # Right side - essential control buttons only
        right_controls = tk.Frame(status_content, bg="#bdc3c7")
        right_controls.pack(side=tk.RIGHT, fill=tk.Y)

        # Essential control buttons with compact styling
        button_configs = [
            ("Auto Pilot", self.toggle_autopilot, "#95a5a6"),  # toggles ON/OFF
            ("Clear", self.clear_output, "#95a5a6"),
            ("Stop All", self.stop_all_current, "#e67e22"),
            ("Stop", self.stop_current, "#e74c3c"),
            ("✕", self.destroy, "#7f8c8d"),  # X symbol for exit to save space
        ]

        for text, command, color in button_configs:
            btn = tk.Button(
                right_controls,
                text=text,
                font=("Segoe UI", 8, "bold"),
                bg=color,
                fg="white",
                relief="flat",
                bd=0,
                padx=8,
                pady=2,
                cursor="hand2",
                command=command,
            )
            btn.pack(side=tk.RIGHT, padx=(4, 0))

            # Hover effects for control buttons
            def make_hover(button, original_color):
                def on_enter(e):
                    # Darken color on hover
                    darker_colors = {
                        "#95a5a6": "#7f8c8d",
                        "#e67e22": "#d35400",
                        "#e74c3c": "#c0392b",
                        "#7f8c8d": "#95a5a6",
                        "#27ae60": "#1e8449",
                        "#2ecc71": "#27ae60",
                        "#8e44ad": "#6c3483",
                    }
                    button.config(bg=darker_colors.get(original_color, original_color))

                def on_leave(e):
                    button.config(bg=original_color)

                return on_enter, on_leave

            enter_func, leave_func = make_hover(btn, color)
            btn.bind("<Enter>", enter_func)
            btn.bind("<Leave>", leave_func)

            # Store stop buttons for state management
            if text == "Auto Pilot":
                self.autopilot_btn = btn

                # Rebind hover to honor enabled (green) vs disabled (gray) state
                def ap_on_enter(e, b=btn):
                    try:
                        # Darker green when enabled, darker gray when disabled
                        b.config(
                            bg=("#1e8449" if self.autopilot_enabled else "#7f8c8d")
                        )
                    except Exception:
                        pass

                def ap_on_leave(e, b=btn):
                    try:
                        # Keep green when enabled, gray when disabled
                        b.config(
                            bg=("#27ae60" if self.autopilot_enabled else "#95a5a6")
                        )
                    except Exception:
                        pass

                btn.bind("<Enter>", ap_on_enter)
                btn.bind("<Leave>", ap_on_leave)
            # note: XAMPP and Tunnel controls are now in the Servers row
            elif text == "Stop":
                self.stop_btn = btn
                btn.config(state=tk.DISABLED)
            elif text == "Stop All":
                self.stop_all_btn = btn
                btn.config(state=tk.DISABLED)

    # ---------- Auto Pilot controls ----------
    def toggle_autopilot(self):
        if self.autopilot_enabled:
            self.stop_autopilot()
        else:
            self.start_autopilot()

    def start_autopilot(self):
        self.autopilot_enabled = True
        if self.autopilot_btn and self.autopilot_btn.winfo_exists():
            self.autopilot_btn.configure(bg="#27ae60")
        self.append_output(
            f"Auto Pilot enabled (interval {int(self.autopilot_interval_ms/1000)}s).\n"
        )
        # kick off first cycle when safe
        self._schedule_autopilot_kickoff(300)

    def stop_autopilot(self):
        self.autopilot_enabled = False
        if self.autopilot_btn and self.autopilot_btn.winfo_exists():
            self.autopilot_btn.configure(bg="#95a5a6")
        if self.autopilot_after_id is not None:
            try:
                self.after_cancel(self.autopilot_after_id)
            except Exception:
                pass
            self.autopilot_after_id = None
        self.append_output("Auto Pilot disabled.\n")

    def _schedule_autopilot_kickoff(self, delay_ms: int):
        # schedule a kickoff that waits for current short tasks to finish
        def kickoff():
            if not self.autopilot_enabled:
                return
            sync_label = "Sync Resolved to MySQL"
            predict_label = "Predict Severity (Mobile)"
            # Only start if neither sync nor predict is running
            if sync_label not in self.processes and predict_label not in self.processes:
                try:
                    self.run_command(sync_label, COMMANDS[sync_label])
                except Exception:
                    # Try again shortly if something transient happens
                    self._schedule_autopilot_kickoff(1000)
            else:
                # Wait and try after a second
                self._schedule_autopilot_kickoff(1000)

        self.autopilot_after_id = self.after(delay_ms, kickoff)

    def clear_output(self):
        self.output.configure(state=tk.NORMAL)
        self.output.delete(1.0, tk.END)
        self.output.configure(state=tk.DISABLED)

    def append_output(self, text: str):
        self.output.configure(state=tk.NORMAL)
        self.output.insert(tk.END, text)
        self.output.see(tk.END)
        self.output.configure(state=tk.DISABLED)

    def run_command(self, label, cmd):
        # Ensure working directory is repo root so artisan works
        cwd = REPO_ROOT
        if not os.path.exists(PHP_ARTISAN):
            messagebox.showerror("Error", f"artisan not found in {REPO_ROOT}")
            return

        # On Windows, use shell=False and pass list
        def worker():
            long_running = label in LONG_RUNNING_LABELS
            self.set_running(
                True, cmd, scope=("single" if long_running else "global"), label=label
            )
            self.append_output(f"> {' '.join(cmd)}\n\n")
            try:
                proc = subprocess.Popen(
                    cmd,
                    cwd=cwd,
                    stdout=subprocess.PIPE,
                    stderr=subprocess.STDOUT,
                    shell=False,
                )
                self.current_process = proc
                self.current_label = label
                # track process by label, disable only that button
                self.processes[label] = proc
                try:
                    # Ensure the button for this label stays disabled while running
                    if label in self.buttons:
                        self.buttons[label].configure(state=tk.DISABLED)
                except Exception:
                    pass
                # Mark server button as active for long-running services
                if label in LONG_RUNNING_LABELS:
                    self._set_server_active(label, True)
                import re

                browser_opened = False
                for line in iter(proc.stdout.readline, b""):
                    if not line:
                        break
                    try:
                        decoded = line.decode("utf-8", errors="ignore")
                    except Exception:
                        decoded = str(line)
                    self.append_output(decoded)
                    # Auto-open browser if PHP server started (parse address)
                    if not browser_opened and label == "Start PHP Server (serve)":
                        match = re.search(
                            r"Server running on \[(http[^\]]+)\]", decoded
                        )
                        if match:
                            url = match.group(1)
                            try:
                                webbrowser.open(url)
                                browser_opened = True
                            except Exception:
                                pass
                    elif not browser_opened and label == "Start Cloudflare Tunnel":
                        # Try to detect the generated trycloudflare URL and open it
                        m2 = re.search(
                            r"(https://[-a-z0-9.]*trycloudflare\.com)",
                            decoded,
                            re.IGNORECASE,
                        )
                        if m2:
                            turl = m2.group(1)
                            # Wait for DNS/edge readiness to avoid NXDOMAIN right after start
                            self.append_output(
                                f"Tunnel URL detected: {turl}\nWaiting for Cloudflare to be ready...\n"
                            )

                            def wait_and_open():
                                import time
                                from urllib import request, error

                                nonlocal browser_opened
                                deadline = time.time() + 45  # max wait 45s
                                last_err = None
                                while time.time() < deadline and not browser_opened:
                                    try:
                                        req = request.Request(turl, method="HEAD")
                                        with request.urlopen(req, timeout=3) as resp:
                                            status = getattr(
                                                resp, "status", resp.getcode()
                                            )
                                        # Any HTTP status means DNS/TLS worked; open browser
                                        try:
                                            webbrowser.open(turl)
                                        except Exception:
                                            pass
                                        browser_opened = True
                                        self.append_output(
                                            "Tunnel is ready. Opened browser.\n"
                                        )
                                        return
                                    except error.HTTPError as he:
                                        # Domain resolved and server responded; treat as ready
                                        try:
                                            webbrowser.open(turl)
                                        except Exception:
                                            pass
                                        browser_opened = True
                                        self.append_output(
                                            "Tunnel is ready. Opened browser.\n"
                                        )
                                        return
                                    except Exception as e:
                                        last_err = e
                                    time.sleep(1.0)
                                # If we exit loop without success, still print the URL for manual attempt
                                self.append_output(
                                    f"Tunnel URL may still be propagating. Try manually: {turl}\n"
                                )

                            threading.Thread(target=wait_and_open, daemon=True).start()
                proc.wait()
                self.append_output(f"\n[exit code {proc.returncode}]\n\n")
            except FileNotFoundError as e:
                missing = (
                    cmd[0] if isinstance(cmd, (list, tuple)) and cmd else "executable"
                )
                self.append_output(
                    f"Error: {e}. Ensure '{missing}' is installed and in PATH.\n"
                )
            except Exception as e:
                self.append_output(f"Unexpected error: {e}\n")
            finally:
                # cleanup
                try:
                    if label in self.processes and self.processes[label] is proc:
                        del self.processes[label]
                except Exception:
                    pass
                try:
                    # Re-enable the button if no other process with same label (avoid duplicates)
                    if label in self.buttons:
                        self.buttons[label].configure(
                            state=(
                                tk.NORMAL
                                if label not in self.processes
                                else tk.DISABLED
                            )
                        )
                except Exception:
                    pass
                # Mark server button as inactive if it was a long-running label
                if label in LONG_RUNNING_LABELS:
                    self._set_server_active(label, False)
                # If no more running processes, clear current pointers
                if not self.processes:
                    self.current_process = None
                    self.current_label = None
                self.set_running(
                    False,
                    cmd,
                    scope=("single" if long_running else "global"),
                    label=label,
                )
                # Re-enable Tunnel button if the Cloudflare process finished
                try:
                    if (
                        label == "Start Cloudflare Tunnel"
                        and hasattr(self, "tunnel_btn")
                        and self.tunnel_btn.winfo_exists()
                    ):
                        self.tunnel_btn.configure(state=tk.NORMAL)
                except Exception:
                    pass
                # Auto Pilot chaining logic: Sync -> Predict -> wait -> Sync ...
                try:
                    if self.autopilot_enabled:
                        sync_label = "Sync Resolved to MySQL"
                        predict_label = "Predict Severity (Mobile)"
                        if label == sync_label and (
                            predict_label not in self.processes
                        ):
                            # chain predict shortly after sync
                            self.append_output("Auto Pilot: starting Predict next...\n")
                            self.autopilot_after_id = self.after(
                                300,
                                lambda: self.run_command(
                                    predict_label, COMMANDS[predict_label]
                                ),
                            )
                        elif label == predict_label:
                            # schedule next cycle after interval
                            delay = getattr(self, "autopilot_interval_ms", 5000)
                            self.append_output(
                                f"Auto Pilot: waiting {int(delay/1000)}s before next cycle...\n"
                            )
                            self.autopilot_after_id = self.after(
                                delay, lambda: self._schedule_autopilot_kickoff(0)
                            )
                except Exception:
                    pass

        threading.Thread(target=worker, daemon=True).start()

    def start_tunnel(self):
        # Start a quick Cloudflare Tunnel to 127.0.0.1:8000
        try:
            if hasattr(self, "tunnel_btn") and self.tunnel_btn.winfo_exists():
                self.tunnel_btn.configure(state=tk.DISABLED)
        except Exception:
            pass
        self.run_command(
            "Start Cloudflare Tunnel",
            [CLOUDFLARED_EXE, "tunnel", "--url", "http://127.0.0.1:8000"],
        )

    # ---------- XAMPP controls ----------
    def _port_open(self, host: str, port: int, timeout: float = 1.0) -> bool:
        try:
            with socket.create_connection((host, port), timeout=timeout):
                return True
        except Exception:
            return False

    def _try_start_service(self, service_names: list[str]) -> bool:
        """Attempt to start a Windows service by names; return True if command accepted.
        We don't rely solely on service return; we'll verify with port probes later.
        """
        for name in service_names:
            try:
                r = subprocess.run(
                    ["sc", "start", name], capture_output=True, text=True
                )
                if (
                    r.returncode == 0
                    or (r.stdout and "RUNNING" in r.stdout.upper())
                    or (
                        r.stderr
                        and "SERVICE HAS NOT BEEN STARTED" not in r.stderr.upper()
                    )
                ):
                    self.append_output(f"Tried starting service '{name}'.\n")
                    return True
            except Exception:
                pass
        return False

    def _start_bat(self, bat_path: str) -> bool:
        if os.path.exists(bat_path):
            try:
                # Run .bat via cmd; don't block UI
                subprocess.Popen(
                    ["cmd.exe", "/c", bat_path],
                    cwd=os.path.dirname(bat_path),
                    stdout=subprocess.DEVNULL,
                    stderr=subprocess.DEVNULL,
                )
                self.append_output(f"Launched: {bat_path}\n")
                return True
            except Exception as e:
                self.append_output(f"Failed to launch {bat_path}: {e}\n")
        else:
            self.append_output(f"Not found: {bat_path}\n")
        return False

    def _try_stop_service(self, service_names: list[str]) -> bool:
        """Attempt to stop a Windows service by names; return True if command accepted."""
        for name in service_names:
            try:
                r = subprocess.run(["sc", "stop", name], capture_output=True, text=True)
                if (
                    r.returncode == 0
                    or (
                        r.stdout
                        and (
                            "STOPPED" in r.stdout.upper()
                            or "STOP_PENDING" in r.stdout.upper()
                        )
                    )
                    or (
                        r.stderr
                        and "SERVICE HAS NOT BEEN STARTED" not in r.stderr.upper()
                    )
                ):
                    self.append_output(f"Tried stopping service '{name}'.\n")
                    return True
            except Exception:
                pass
        return False

    def _stop_bat(self, bat_path: str) -> bool:
        if os.path.exists(bat_path):
            try:
                subprocess.Popen(
                    ["cmd.exe", "/c", bat_path],
                    cwd=os.path.dirname(bat_path),
                    stdout=subprocess.DEVNULL,
                    stderr=subprocess.DEVNULL,
                )
                self.append_output(f"Launched: {bat_path}\n")
                return True
            except Exception as e:
                self.append_output(f"Failed to launch {bat_path}: {e}\n")
        return False

    def _taskkill_by_image(self, exe_names: list[str]):
        for name in exe_names:
            try:
                subprocess.run(
                    ["taskkill", "/F", "/IM", name, "/T"], capture_output=True
                )
                self.append_output(f"Killed process image: {name}\n")
            except Exception:
                pass

    def start_xampp(self):
        """Start Apache (80) and MySQL (3306) from XAMPP. Tries Windows services first,
        then falls back to XAMPP batch files. Verifies by probing ports.
        """
        # Busy guard and visual cue (cursor only, no disable to avoid white flash)
        if self.xampp_busy:
            return
        self.xampp_busy = True
        try:
            if self.xampp_btn and self.xampp_btn.winfo_exists():
                self.xampp_btn.configure(cursor="watch")
        except Exception:
            pass

        def worker():
            self.append_output("Starting XAMPP services (Apache + MySQL)...\n")
            # Apache
            if self._port_open("127.0.0.1", 80) or self._port_open("127.0.0.1", 443):
                self.append_output(
                    "Apache appears to be already running (port 80/443 is in use).\n"
                )
                apache_ok = True
            else:
                started = self._try_start_service(
                    ["Apache2.4", "apache2.4"]
                ) or self._start_bat(APACHE_START_BAT)
                # Wait up to 20s for port 80/443
                deadline = time.time() + 20
                while time.time() < deadline:
                    if self._port_open("127.0.0.1", 80) or self._port_open(
                        "127.0.0.1", 443
                    ):
                        self.append_output("Apache is running.\n")
                        apache_ok = True
                        break
                    time.sleep(1)
                else:
                    self.append_output(
                        "Warning: Apache did not start within 20s. Check XAMPP Control Panel.\n"
                    )
                    apache_ok = False

            # MySQL
            if self._port_open("127.0.0.1", 3306):
                self.append_output(
                    "MySQL appears to be already running (port 3306 is in use).\n"
                )
                mysql_ok = True
            else:
                started = self._try_start_service(
                    ["mysql", "MySQL", "xamppmysql", "xamppmysqlservice"]
                ) or self._start_bat(MYSQL_START_BAT)
                # Wait up to 20s for port 3306
                deadline = time.time() + 20
                while time.time() < deadline:
                    if self._port_open("127.0.0.1", 3306):
                        self.append_output("MySQL is running.\n")
                        mysql_ok = True
                        break
                    time.sleep(1)
                else:
                    self.append_output(
                        "Warning: MySQL did not start within 20s. Check XAMPP Control Panel.\n"
                    )
                    mysql_ok = False

            self.append_output("XAMPP start routine finished.\n")
            # Show unified active state on button when both are running
            try:
                self._set_server_active("XAMPP", bool(apache_ok and mysql_ok))
            except Exception:
                pass
            # Clear busy and restore cursor
            self.xampp_busy = False
            try:
                if self.xampp_btn and self.xampp_btn.winfo_exists():
                    self.xampp_btn.configure(cursor="hand2")
            except Exception:
                pass

        threading.Thread(target=worker, daemon=True).start()

    def stop_xampp(self):
        """Stop Apache and MySQL started by XAMPP. Try services, then stop .bat, then taskkill."""
        # Busy guard and visual cue
        if self.xampp_busy:
            return
        self.xampp_busy = True
        try:
            if self.xampp_btn and self.xampp_btn.winfo_exists():
                self.xampp_btn.configure(cursor="watch")
        except Exception:
            pass

        def worker():
            self.append_output("Stopping XAMPP services (Apache + MySQL)...\n")
            # Try to stop Apache
            self._try_stop_service(["Apache2.4", "apache2.4"]) or self._stop_bat(
                APACHE_STOP_BAT
            )
            # Try to stop MySQL
            self._try_stop_service(
                ["mysql", "MySQL", "xamppmysql", "xamppmysqlservice"]
            ) or self._stop_bat(MYSQL_STOP_BAT)

            # Wait up to 20s for ports to close; if not, kill processes
            deadline = time.time() + 20
            while time.time() < deadline:
                ap_open = self._port_open("127.0.0.1", 80) or self._port_open(
                    "127.0.0.1", 443
                )
                my_open = self._port_open("127.0.0.1", 3306)
                if not ap_open and not my_open:
                    break
                time.sleep(1)

            # Fallback kill if still open
            if self._port_open("127.0.0.1", 80) or self._port_open("127.0.0.1", 443):
                self._taskkill_by_image(["httpd.exe", "apache.exe"])
            if self._port_open("127.0.0.1", 3306):
                self._taskkill_by_image(["mysqld.exe", "mysql.exe"])

            ap_open = self._port_open("127.0.0.1", 80) or self._port_open(
                "127.0.0.1", 443
            )
            my_open = self._port_open("127.0.0.1", 3306)
            if not ap_open:
                self.append_output("Apache is stopped.\n")
            else:
                self.append_output("Warning: Apache still appears to be running.\n")
            if not my_open:
                self.append_output("MySQL is stopped.\n")
            else:
                self.append_output("Warning: MySQL still appears to be running.\n")

            # Update button state and UI
            self._set_server_active("XAMPP", False)
            # Clear busy and restore cursor
            self.xampp_busy = False
            try:
                if self.xampp_btn and self.xampp_btn.winfo_exists():
                    self.xampp_btn.configure(cursor="hand2")
            except Exception:
                pass

        threading.Thread(target=worker, daemon=True).start()

    def toggle_xampp(self):
        # If both ports in use, consider XAMPP running
        running = (
            self._port_open("127.0.0.1", 80) or self._port_open("127.0.0.1", 443)
        ) and self._port_open("127.0.0.1", 3306)
        if running:
            self.stop_xampp()
        else:
            self.start_xampp()

    def stop_current(self):
        # Attempt to stop the currently running process (and its children on Windows)
        proc = self.current_process
        if proc is None or proc.poll() is not None:
            return
        try:
            self.append_output("\nStopping current process...\n")
            if os.name == "nt":
                # Kill process tree on Windows
                subprocess.run(
                    ["taskkill", "/F", "/T", "/PID", str(proc.pid)], capture_output=True
                )
            else:
                proc.terminate()
        except Exception as e:
            self.append_output(f"Stop failed: {e}\n")

    def stop_all_current(self):
        if not self.processes:
            # Also ensure Auto Pilot is off
            if self.autopilot_enabled:
                self.stop_autopilot()
            return
        self.append_output("\nStopping all running processes...\n")
        for label, proc in list(self.processes.items()):
            try:
                if proc and proc.poll() is None:
                    if os.name == "nt":
                        subprocess.run(
                            ["taskkill", "/F", "/T", "/PID", str(proc.pid)],
                            capture_output=True,
                        )
                    else:
                        proc.terminate()
            except Exception as e:
                self.append_output(f"Stop failed for {label}: {e}\n")
        self.processes.clear()
        self.current_process = None
        self.current_label = None
        # Reset server visuals for long-running labels immediately
        try:
            for lbl in list(LONG_RUNNING_LABELS):
                self._set_server_active(lbl, False)
        except Exception:
            pass
        # Also stop XAMPP if it appears to be running
        try:
            ap_open = self._port_open("127.0.0.1", 80) or self._port_open(
                "127.0.0.1", 443
            )
            my_open = self._port_open("127.0.0.1", 3306)
            if ap_open or my_open:
                self.stop_xampp()
        except Exception:
            pass
        # Re-enable all buttons
        for label, b in self.buttons.items():
            if b.winfo_exists():  # Check if button still exists
                b.configure(state=tk.NORMAL)
        # Turn off Auto Pilot after stopping all
        if self.autopilot_enabled:
            self.stop_autopilot()

    def set_running(
        self,
        is_running: bool,
        cmd=None,
        scope: str = "global",
        label: str | None = None,
    ):
        self.running = is_running
        if is_running:
            self.title("Admin Control Panel • Running…")
            if scope == "single" and label:
                self.status_label.configure(
                    text=f"Running: {' '.join(cmd) if cmd else ''}"
                )
                # Only disable the button for this label
                try:
                    if label in self.buttons:
                        self.buttons[label].configure(state=tk.DISABLED)
                except Exception:
                    pass
                # Start spinner if this is the first active process
                if len(self.processes) <= 1:
                    try:
                        self.progress.start(12)
                    except Exception:
                        pass
            else:
                self.status_label.configure(
                    text=f"Running: {' '.join(cmd) if cmd else ''}"
                )
                try:
                    self.progress.start(12)
                except Exception:
                    pass
                for b in self.command_buttons:
                    if b.winfo_exists():  # Check if button still exists
                        b.configure(state=tk.DISABLED)
            # Stop buttons
            if hasattr(self, "stop_btn") and self.stop_btn.winfo_exists():
                self.stop_btn.configure(state=tk.NORMAL)
            if hasattr(self, "stop_all_btn") and self.stop_all_btn.winfo_exists():
                self.stop_all_btn.configure(
                    state=tk.NORMAL if self.processes else tk.DISABLED
                )
        else:
            # When stopping, keep other running labels disabled if they still run
            if scope == "single" and label:
                # Re-enable only if no process for that label remains
                try:
                    if label in self.buttons and label not in self.processes:
                        self.buttons[label].configure(state=tk.NORMAL)
                except Exception:
                    pass
                # Spinner: stop if no processes left, else keep spinning
                if not self.processes:
                    self.title("Admin Control Panel")
                    self.status_label.configure(text="Ready")
                    try:
                        self.progress.stop()
                    except Exception:
                        pass
            else:
                # Global stop: re-enable all, except those still running
                self.title("Admin Control Panel")
                self.status_label.configure(
                    text="Ready" if not self.processes else "Running…"
                )
                try:
                    if not self.processes:
                        self.progress.stop()
                except Exception:
                    pass
                for lbl, b in self.buttons.items():
                    if b.winfo_exists():  # Check if button still exists
                        b.configure(
                            state=(tk.DISABLED if lbl in self.processes else tk.NORMAL)
                        )
            # Update stop buttons state
            if hasattr(self, "stop_btn") and self.stop_btn.winfo_exists():
                self.stop_btn.configure(
                    state=(tk.NORMAL if self.processes else tk.DISABLED)
                )
            if hasattr(self, "stop_all_btn") and self.stop_all_btn.winfo_exists():
                self.stop_all_btn.configure(
                    state=(tk.NORMAL if self.processes else tk.DISABLED)
                )

    def _set_server_active(self, label: str, active: bool):
        """Update button background to green when active, blue when inactive."""
        try:
            self.server_states[label] = active
            btn = self.buttons.get(label)
            if not btn and label == "XAMPP":
                btn = self.xampp_btn
            if btn and btn.winfo_exists():
                btn.configure(bg=("#2ecc71" if active else "#3498db"))
        except Exception:
            pass


if __name__ == "__main__":
    # Optional: allow pythonw.exe to run without console
    app = AdminControlPanel()
    app.mainloop()
