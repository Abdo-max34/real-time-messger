import { usePage } from "@inertiajs/react";
import ReactMarkdown from "react-markdown";
import React from "react";
import UserAvatar from "./UserAvatar";
import { formatMessageDateLong } from "@/helpers";

const MessageItem = ({ message, attachementClick }) => {
    const currentUser = usePage().props.auth.user;
    const isCurrentUser = message.sender_id === currentUser.id;

    return (
        <div className={`chat ${isCurrentUser ? "chat-end" : "chat-start"}`}>
            <UserAvatar user={message.sender} />
            <div className="chat-header">
                {!isCurrentUser && message.sender.name}
                <time className="text-xs opacity-50 ml-2 text-slate-200">
                    {formatMessageDateLong(message.created_at)}
                </time>
            </div>
            <div
                className={`chat-bubble relative ${
                    isCurrentUser ? "chat-bubble-info" : ""
                }`}
            >
                <div className="chat-message">
                    <div className="chat-message-content">
                        <ReactMarkdown>{message.message}</ReactMarkdown>
                    </div>
                </div>
                {/* Render attachments or handle attachment click if needed */}
            </div>
        </div>
    );
};

export default MessageItem;
